<?php
declare(strict_types=1);

requireMethod('POST');
requireAuthTokenCookie();

use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Application\Usuario\UseCase\BuscarOCrearUsuario;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

$type = (string) ($_GET['type'] ?? '');

try {
    $conn = MysqlConnection::get();
    $usuarioRepo = new MySqlUsuarioRepository($conn);

    // =========================================================
    // type=usuarios-create  (POST)
    // =========================================================
    if ($type === 'usuarios-create') {
        $input = readJsonBody(true);

        $useCase = new BuscarOCrearUsuario($usuarioRepo);
        $usuario = $useCase->execute($input);

        if (!empty($input['nombre'])) {
            $perfilDto = ActualizarPerfilDTO::fromArray($input);
            $perfil = UsuarioFactory::crearPerfil($usuario->uuid(), $perfilDto);
            $usuarioRepo->savePerfil($perfil);
        }

        jsonResponse(
            vp2_ok('Usuario creado correctamente', [
                'uuid' => $usuario->uuid()->toString(),
                'estado' => $usuario->estado()->value,
            ]),
            201,
        );
    }

    // =========================================================
    // type=clienteAnual-create  (POST)
    // =========================================================
    if ($type === 'clienteAnual-create') {
        $input = readJsonBody(true);

        if (empty($input['matricula'])) {
            jsonResponse(
                vp2_err('La matrícula es obligatoria', 'BAD_MATRICULA'),
                400,
            );
        }
        if (empty($input['fecha_inicio']) || empty($input['fecha_fin'])) {
            jsonResponse(
                vp2_err('Fechas de abono obligatorias', 'BAD_FECHAS'),
                400,
            );
        }

        $conn->beginTransaction();
        try {
            // 1. Usuario (busca por email o crea, evita duplicados)
            $useCase = new BuscarOCrearUsuario($usuarioRepo);
            $usuario = $useCase->execute(
                array_merge($input, ['tipo_rol' => 'cliente_anual']),
            );

            // 2. Perfil
            $perfilDto = ActualizarPerfilDTO::fromArray($input);
            $perfil = UsuarioFactory::crearPerfil($usuario->uuid(), $perfilDto);
            $usuarioRepo->savePerfil($perfil);

            // 3. Abono
            $abonoDto = CrearAbonoDTO::fromArray(
                array_merge($input, [
                    'usuario_uuid' => $usuario->uuid()->toString(),
                ]),
            );
            $abono = UsuarioFactory::crearAbono($abonoDto);
            new MySqlAbonoRepository($conn)->save($abono);

            $conn->commit();

            jsonResponse(
                vp2_ok('OK', ['uuid' => $usuario->uuid()->toString()]),
            );
        } catch (\Throwable $e) {
            $conn->rollBack();
            jsonResponse(
                vp2_err('Error creando cliente anual', 'CREATE_ERROR', [
                    'details' => $e->getMessage(),
                ]),
                500,
            );
        }
    }

    jsonResponse(
        vp2_err('type inválido', 'BAD_TYPE', [
            'allowed' => ['usuarios-create', 'clienteAnual-create'],
        ]),
        400,
    );
} catch (\App\Application\Shared\Schema\SchemaValidationException $e) {
    jsonResponse(
        vp2_err('Datos inválidos', 'BAD_INPUT', $e->toApiArray()),
        422,
    );
} catch (\Throwable $e) {
    jsonResponse(
        vp2_err('Error del servidor', 'SERVER_ERROR', [
            'details' => $e->getMessage(),
        ]),
        500,
    );
}
