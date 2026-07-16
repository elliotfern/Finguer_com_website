<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Carrito\Controller;

use App\Application\Carrito\UseCase\ObtenerCarritoUseCase;
use App\Domain\Carrito\Entity\Carrito;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;

final class ObtenerCarritoController
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

        $session = trim((string) ($_GET['session'] ?? ''));

        try {
            $conn = MysqlConnection::get();
            $useCase = new ObtenerCarritoUseCase(
                new MySqlCarritoRepository($conn),
            );

            $carrito = $useCase->execute($session);

            echo json_encode(
                [
                    'status' => 'success',
                    'data' => self::toArray($carrito),
                ],
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
            );
        } catch (\InvalidArgumentException $e) {
            $isNotFound = $e->getMessage() === 'NOT_FOUND';

            http_response_code($isNotFound ? 404 : 400);
            echo json_encode([
                'status' => 'error',
                'message' => $isNotFound
                    ? 'Carrito no encontrado'
                    : 'Falta parámetro session',
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ObtenerCarritoController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }

    private static function toArray(Carrito $carrito): array
    {
        return [
            'session' => $carrito->session(),
            'subtotal' => $carrito->subtotalSinIva(),
            'iva_total' => $carrito->ivaTotal(),
            'total' => $carrito->totalConIva(),
            'hash' => $carrito->hash(),
            'updated_at' => $carrito->updatedAt()?->format('Y-m-d H:i:s'),
            'snapshot' => $carrito->toSnapshotArray(),
        ];
    }
}
