<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Application\Usuario\UseCase\ObtenerUsuarioUseCase;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class ObtenerUsuarioController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
            exit();
        }

        requireAuthTokenCookie();

        $user = auth_user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'No autoritzat',
            ]);
            exit();
        }

        $uuidStr = (string) ($_GET['uuid'] ?? '');

        try {
            $conn = MysqlConnection::get();
            $useCase = new ObtenerUsuarioUseCase(
                new MySqlUsuarioRepository($conn),
            );

            $dto = $useCase->execute($uuidStr);

            echo json_encode([
                'status' => 'success',
                'message' => 'OK',
                'data' => $dto->toArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'MISSING_UUID' => 'Falta parámetro uuid',
                'BAD_UUID' => 'UUID inválido',
                'NOT_FOUND' => 'Usuario no encontrado',
                default => 'Datos inválidos',
            };
            $code = match ($e->getMessage()) {
                'NOT_FOUND' => 404,
                default => 400,
            };
            http_response_code($code);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ObtenerUsuarioController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
