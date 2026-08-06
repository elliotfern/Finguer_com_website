<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Carrito\Controller;

use App\Application\Carrito\DTO\GuardarCarritoDTO;
use App\Application\Carrito\Exception\ReglaNegocioException;
use App\Application\Carrito\UseCase\GuardarCarritoUseCase;
use App\Infrastructure\EntryPoint\Http\Response\ApiResponse;
use App\Infrastructure\EntryPoint\Http\Response\JsonResponseEmitter;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use App\Infrastructure\Persistence\MySql\Catalogo\MySqlServicioRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;

final class GuardarCarritoController
{
    public static function handle(): void
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: POST, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        $emitter = new JsonResponseEmitter();

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $emitter->emit(
                ApiResponse::error(
                    'Method not allowed',
                    'method_not_allowed',
                    httpCode: 405,
                ),
            );
            return;
        }

        $emitter->emit(self::process(self::readInput()));
    }

    /**
     * @return array<string, mixed>
     */
    private static function readInput(): array
    {
        $ct = $_SERVER['CONTENT_TYPE'] ?? '';

        if (stripos($ct, 'application/json') !== false) {
            $raw = file_get_contents('php://input');
            $input = json_decode($raw ?: '{}', true);

            return is_array($input) ? $input : [];
        }

        return $_POST;
    }

    /**
     * @param array<string, mixed> $input
     */
    private static function process(array $input): ApiResponse
    {
        try {
            $conn = MysqlConnection::get();
            $useCase = new GuardarCarritoUseCase(
                new MySqlCarritoRepository($conn),
                new MySqlServicioRepository($conn),
            );

            $dto = GuardarCarritoDTO::fromArray($input);
            $carrito = $useCase->execute($dto);

            return ApiResponse::ok('Carrito guardado correctamente', [
                'diasReserva' => $carrito->diasReserva(),
                'lineas' => array_map(
                    static fn($l) => [
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
            ]);
        } catch (ReglaNegocioException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                $e->codigoRegla,
                httpCode: 422,
            );
        } catch (\InvalidArgumentException $e) {
            return ApiResponse::error(
                $e->getMessage(),
                'invalid_argument',
                httpCode: 400,
            );
        } catch (\Throwable $e) {
            error_log(
                '[FINGUER] GuardarCarritoController: ' . $e->getMessage(),
            );

            return ApiResponse::error(
                'Error interno',
                'internal_error',
                httpCode: 500,
            );
        }
    }
}
