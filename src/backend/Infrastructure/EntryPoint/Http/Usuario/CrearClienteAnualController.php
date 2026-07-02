<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario;

use App\Application\Shared\Schema\SchemaValidationException;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Application\Usuario\UseCase\BuscarOCrearUsuario;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class CrearClienteAnualController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
            exit();
        }

        requireAuthTokenCookie();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'JSON inválido',
            ]);
            exit();
        }

        if (empty($data['matricula'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'La matrícula es obligatoria',
            ]);
            exit();
        }
        if (empty($data['fecha_inicio']) || empty($data['fecha_fin'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Las fechas de abono son obligatorias',
            ]);
            exit();
        }

        $conn = MysqlConnection::get();
        $usuarioRepo = new MySqlUsuarioRepository($conn);

        $conn->beginTransaction();
        try {
            // 1. Usuario (busca por email o crea, evita duplicados)
            $useCase = new BuscarOCrearUsuario($usuarioRepo);
            $usuario = $useCase->execute(
                array_merge($data, ['tipo_rol' => 'cliente_anual']),
            );

            // 2. Perfil
            $perfilDto = ActualizarPerfilDTO::fromArray($data);
            $perfil = UsuarioFactory::crearPerfil($usuario->uuid(), $perfilDto);
            $usuarioRepo->savePerfil($perfil);

            // 3. Abono
            $abonoDto = CrearAbonoDTO::fromArray(
                array_merge($data, [
                    'usuario_uuid' => $usuario->uuid()->toString(),
                ]),
            );
            $abono = UsuarioFactory::crearAbono($abonoDto);
            new MySqlAbonoRepository($conn)->save($abono);

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'usuario_uuid_hex' => str_replace(
                    '-',
                    '',
                    $usuario->uuid()->toString(),
                ),
                'message' => 'Cliente anual creado correctamente',
            ]);
        } catch (SchemaValidationException $e) {
            $conn->rollBack();
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\Throwable $e) {
            $conn->rollBack();
            http_response_code(500);
            error_log(
                '[FINGUER] CrearClienteAnualController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
