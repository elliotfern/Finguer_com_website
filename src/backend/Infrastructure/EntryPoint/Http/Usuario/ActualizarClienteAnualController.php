<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario;

use App\Application\Shared\Schema\SchemaValidationException;
use App\Application\Usuario\UseCase\ActualizarClienteAnualUseCase;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class ActualizarClienteAnualController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
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

        try {
            $conn = MysqlConnection::get();
            $useCase = new ActualizarClienteAnualUseCase(
                $conn,
                new MySqlUsuarioRepository($conn),
                new MySqlAbonoRepository($conn),
            );
            $usuario = $useCase->execute($data);

            echo json_encode([
                'status' => 'success',
                'message' => 'Cliente anual actualizado correctamente',
                'data' => [
                    'usuario_uuid_hex' => str_replace(
                        '-',
                        '',
                        $usuario->uuid()->toString(),
                    ),
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'MISSING_UUID' => 'UUID requerido',
                'NOT_FOUND' => 'Usuario no encontrado',
                default => 'Datos inválidos',
            };
            $code = $e->getMessage() === 'NOT_FOUND' ? 404 : 400;
            http_response_code($code);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (SchemaValidationException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ActualizarClienteAnualController: ' .
                    $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
