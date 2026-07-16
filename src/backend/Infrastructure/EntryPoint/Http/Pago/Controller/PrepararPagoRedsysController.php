<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Pago\Controller;

use App\Application\Pago\UseCase\PrepararPagoRedsysUseCase;
use App\Infrastructure\Pago\Redsys\RedsysPasarelaPago;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\LocalizadorGenerator;

final class PrepararPagoRedsysController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type');

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

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'No se enviaron datos válidos.',
            ]);
            exit();
        }

        $session = trim((string) ($data['session'] ?? ''));

        try {
            $conn = MysqlConnection::get();
            $useCase = new PrepararPagoRedsysUseCase(
                new MySqlCarritoRepository($conn),
                new LocalizadorGenerator($conn),
                RedsysPasarelaPago::fromEnv(),
            );

            $result = $useCase->execute($session);

            echo json_encode([
                'status' => 'success',
                'message' => 'Datos válidos.',
                'data' => [
                    'params' => $result->params->params,
                    'signature' => $result->params->signature,
                    'idReserva' => $result->localizador,
                ],
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'MISSING_SESSION' => 'Falta session.',
                'CARRITO_NOT_FOUND'
                    => 'Carrito no encontrado para esta session.',
                'IMPORTE_INVALIDO' => 'Importe inválido.',
                default => 'Datos inválidos',
            };

            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] PrepararPagoRedsysController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
