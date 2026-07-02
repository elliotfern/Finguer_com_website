<?php
declare(strict_types=1);

requireMethod('PUT');
requireAuthTokenCookie();

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

$type = (string) ($_GET['type'] ?? '');

try {
    $conn = MysqlConnection::get();
    $usuarioRepo = new MySqlUsuarioRepository($conn);

    // =========================================================
    // type=usuarios-update  (PUT)
    // =========================================================
    if ($type === 'usuarios-update') {
        $input = readJsonBody(true);

        $uuidStr = trim((string) ($input['uuid'] ?? ''));
        if ($uuidStr === '') {
            jsonResponse(vp2_err('UUID requerido', 'BAD_UUID'), 400);
        }
        $uuid = UsuarioUuid::fromString($uuidStr);

        $existente = $usuarioRepo->findByUuid($uuid);
        if ($existente === null) {
            jsonResponse(vp2_err('Usuario no encontrado', 'NOT_FOUND'), 404);
        }

        $email = Email::fromString($input['email'] ?? '');

        // Email único excluyendo el propio
        $otro = $usuarioRepo->findByEmail($email);
        if ($otro !== null && !$otro->uuid()->equals($uuid)) {
            jsonResponse(
                vp2_err('Ya existe un usuario con este email', 'EMAIL_EXISTS'),
                409,
            );
        }

        $plainPass = trim((string) ($input['password'] ?? ''));
        $passwordHash =
            $plainPass !== ''
                ? password_hash($plainPass, PASSWORD_DEFAULT)
                : $existente->password(); // conserva el actual si no envían uno nuevo

        $usuarioActualizado = Usuario::fromDatabase(
            uuid: $uuid,
            email: $email,
            estado: $existente->estado(),
            rol: Rol::tryFrom($input['tipo_rol'] ?? '') ?? $existente->rol(),
            locale: Locale::tryFrom($input['locale'] ?? '') ??
                $existente->locale(),
            password: $passwordHash,
        );
        $usuarioRepo->save($usuarioActualizado);

        if (!empty($input['nombre'])) {
            $perfilDto = ActualizarPerfilDTO::fromArray($input);
            $perfil = UsuarioFactory::crearPerfil($uuid, $perfilDto);
            $usuarioRepo->savePerfil($perfil);
        }

        jsonResponse(
            vp2_ok('Usuario actualizado correctamente', ['uuid' => $uuidStr]),
            200,
        );
    }

    // =========================================================
    // type=clienteAnual-update  (PUT)
    // =========================================================
    if ($type === 'clienteAnual-update') {
        $input = readJsonBody(true);

        $uuidStr = trim((string) ($input['uuid'] ?? ''));
        if ($uuidStr === '') {
            jsonResponse(vp2_err('UUID requerido', 'MISSING_UUID'), 400);
        }
        if (empty($input['matricula'])) {
            jsonResponse(
                vp2_err('La matrícula es obligatoria', 'BAD_MATRICULA'),
                400,
            );
        }

        $uuid = UsuarioUuid::fromString($uuidStr);

        $usuarioExistente = $usuarioRepo->findByUuid($uuid);
        if ($usuarioExistente === null) {
            jsonResponse(vp2_err('Usuario no encontrado', 'NOT_FOUND'), 404);
        }

        $conn->beginTransaction();
        try {
            // 1. Usuario
            $usuarioActualizado = Usuario::fromDatabase(
                uuid: $uuid,
                email: Email::fromString(
                    $input['email'] ?? $usuarioExistente->email()->value(),
                ),
                estado: $usuarioExistente->estado(),
                rol: $usuarioExistente->rol(),
                locale: Locale::tryFrom($input['locale'] ?? '') ??
                    $usuarioExistente->locale(),
                password: $usuarioExistente->password(),
            );
            $usuarioRepo->save($usuarioActualizado);

            // 2. Perfil
            $perfilDto = ActualizarPerfilDTO::fromArray($input);
            $perfil = UsuarioFactory::crearPerfil($uuid, $perfilDto);
            $usuarioRepo->savePerfil($perfil);

            // 3. Abono: actualizar si existe, crear si no
            $abonoRepo = new MySqlAbonoRepository($conn);
            $abonoExistente = $abonoRepo->findByUsuarioUuid($uuid)[0] ?? null;

            $abono = Abono::fromDatabase(
                id: $abonoExistente?->id() ?? UsuarioUuid::generate(),
                usuarioUuid: $uuid,
                estado: AbonoEstado::tryFrom($input['estado'] ?? '') ??
                    ($abonoExistente?->estado() ?? AbonoEstado::Activo),
                fechaInicio: new \DateTimeImmutable(
                    $input['fecha_inicio'] ??
                        $abonoExistente?->fechaInicio()->format('Y-m-d'),
                ),
                fechaFin: new \DateTimeImmutable(
                    $input['fecha_fin'] ??
                        $abonoExistente?->fechaFin()->format('Y-m-d'),
                ),
                limiteReservas: (int) ($input['limite_reservas'] ??
                    ($abonoExistente?->limiteReservas() ?? 10)),
                matricula: Matricula::fromString($input['matricula']),
                vehiculo: $input['vehiculo'] ?? null,
                observaciones: $input['observaciones'] ?? null,
            );
            $abonoRepo->save($abono);

            $conn->commit();

            jsonResponse(vp2_ok('OK', ['uuid' => $uuidStr]));
        } catch (\Throwable $e) {
            $conn->rollBack();
            jsonResponse(
                vp2_err('Error actualizando cliente anual', 'UPDATE_ERROR', [
                    'details' => $e->getMessage(),
                ]),
                500,
            );
        }
    }

    jsonResponse(
        vp2_err('type inválido', 'BAD_TYPE', [
            'allowed' => ['usuarios-update', 'clienteAnual-update'],
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
