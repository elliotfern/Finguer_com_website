<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Carrito;

use App\Application\Carrito\DTO\GuardarCarritoDTO;
use App\Application\Carrito\Exception\ReglaNegocioException;
use App\Application\Carrito\UseCase\GuardarCarritoUseCase;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use App\Infrastructure\Persistence\MySql\Catalogo\MySqlServicioRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;

final class GuardarCarritoController
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

        $ct = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw ?: '{}', true);
            $input = is_array($input) ? $input : [];
        } else {
            $input = $_POST ?? [];
        }

        try {
            $conn = MysqlConnection::get();
            $useCase = new GuardarCarritoUseCase(
                new MySqlCarritoRepository($conn),
                new MySqlServicioRepository($conn),
            );

            $dto = GuardarCarritoDTO::fromArray($input);
            $carrito = $useCase->execute($dto);

            echo json_encode([
                'status' => 'success',
                'data' => [
                    'diasReserva' => $carrito->diasReserva(),
                    'lineas' => array_map(
                        fn($l) => [
                            'codigo' => $l->codigo,
                            'descripcion' => $l->descripcion,
                            'cantidad' => $l->cantidad,
                            'iva_percent' => $l->ivaPercent,
                            'base' => $l->base,
                            'iva' => $l->iva,
                            'total' => $l->total,
                        ],
                        $carrito->lineas(),
                    ),
                    'subtotal' => $carrito->subtotalSinIva(),
                    'iva_total' => $carrito->ivaTotal(),
                    'total' => $carrito->totalConIva(),
                    'hash' => $carrito->hash(),
                ],
            ]);
        } catch (ReglaNegocioException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'codigo' => $e->codigoRegla,
                'message' => $e->getMessage(),
            ]);
        } catch (\InvalidArgumentException $e) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] GuardarCarritoController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
