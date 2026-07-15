<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva;

use App\Application\Reserva\Schema\ReservaSchema;
use App\Application\Reserva\UseCase\CrearReservaUseCase;
use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Shared\Schema\SchemaValidationException;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use App\Infrastructure\Persistence\MySql\Catalogo\MySqlServicioRepository;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class CrearReservaController
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
            $validado = SchemaProcessor::process($data, ReservaSchema::crear());

            $conn = MysqlConnection::get();
            $useCase = new CrearReservaUseCase(
                new MySqlCarritoRepository($conn),
                new MySqlUsuarioRepository($conn),
                new MySqlServicioRepository($conn),
                new MySqlReservaRepository($conn),
            );

            $reserva = $useCase->execute($validado);

            echo json_encode([
                'status' => 'success',
                'message' => 'Reserva creada correctamente',
                'data' => [
                    'reserva_id' => $reserva->id(),
                    'localizador' => $reserva->localizador(),
                    'subtotal' => $reserva->subtotalCalculado(),
                    'iva' => $reserva->ivaCalculado(),
                    'total' => $reserva->totalCalculado(),
                ],
            ]);
        } catch (SchemaValidationException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match (true) {
                $e->getMessage() === 'CARRITO_NOT_FOUND'
                    => 'Carrito no encontrado para esta sesión',
                $e->getMessage() === 'USUARIO_NO_VALIDO'
                    => 'Usuario no encontrado o no activo',
                str_starts_with($e->getMessage(), 'SERVICIO_NO_ENCONTRADO')
                    => 'Servicio no encontrado en catálogo',
                default => 'Datos inválidos',
            };

            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log('[FINGUER] CrearReservaController: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
