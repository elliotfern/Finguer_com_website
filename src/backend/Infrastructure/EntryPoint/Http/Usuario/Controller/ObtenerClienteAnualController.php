<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Application\Usuario\UseCase\ObtenerClienteAnualUseCase;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;

final class ObtenerClienteAnualController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
            exit();
        }

        requireAuthTokenCookie();

        $me = auth_user();
        $role = (string) ($me['role'] ?? '');

        if ($role !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'No autoritzat',
            ]);
            exit();
        }

        $uuidStr = isset($_GET['uuid']) ? trim((string) $_GET['uuid']) : '';

        try {
            $conn = MysqlConnection::get();
            $useCase = new ObtenerClienteAnualUseCase(
                new MySqlUsuarioRepository($conn),
                new MySqlAbonoRepository($conn),
            );

            $dto = $useCase->execute($uuidStr);

            echo json_encode([
                'status' => 'success',
                'data' => $dto->toArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'MISSING_UUID' => 'Falta parámetro uuid',
                'BAD_UUID' => 'UUID inválido',
                'NOT_FOUND' => 'Usuario no encontrado',
                default => 'Datos inválidos',
            };
            $code = $e->getMessage() === 'NOT_FOUND' ? 404 : 400;

            http_response_code($code);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ObtenerClienteAnualController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
