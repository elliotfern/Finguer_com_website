<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\UseCase\ObtenerReservaUseCase;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class ObtenerReservaController
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

        $id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetro id inválido',
            ]);
            exit();
        }

        try {
            $conn = MysqlConnection::get();
            $useCase = new ObtenerReservaUseCase(
                new MySqlReservaRepository($conn),
                new MySqlUsuarioRepository($conn),
            );

            $dto = $useCase->execute($id);

            echo json_encode([
                'status' => 'success',
                'data' => $dto->toArray(),
            ]);
        } catch (ReservaNotFoundException $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Reserva no encontrada',
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ObtenerReservaController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
