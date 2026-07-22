<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\Schema\ReservaSchema;
use App\Application\Reserva\UseCase\ActualizarDatosReservaUseCase;
use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Shared\Schema\SchemaValidationException;
use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlVerificadorFactura;

final class ActualizarDatosReservaController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'PUT') {
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
                'message' => 'No autorizado',
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
            $validado = SchemaProcessor::process(
                $data,
                ReservaSchema::actualizarDatos(),
            );

            $conn = MysqlConnection::get();
            $useCase = new ActualizarDatosReservaUseCase(
                new MySqlReservaRepository($conn),
                new MySqlVerificadorFactura($conn),
            );

            $reserva = $useCase->execute($validado);

            echo json_encode([
                'status' => 'success',
                'message' => 'Reserva actualizada correctamente',
                'data' => ['localizador' => $reserva->localizador()],
            ]);
        } catch (SchemaValidationException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (ReservaNotFoundException $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Reserva no encontrada',
            ]);
        } catch (ReservaConFacturaException $e) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
                'data' => ['id' => $e->id],
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ActualizarDatosReservaController: ' .
                    $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
