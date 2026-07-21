<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\Schema\ReservaSchema;
use App\Application\Reserva\UseCase\ActualizarReservaAnualUseCase;
use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Shared\Schema\SchemaValidationException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;

final class ActualizarReservaAnualController
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
                ReservaSchema::actualizarAnual(),
            );

            $conn = MysqlConnection::get();
            $useCase = new ActualizarReservaAnualUseCase(
                new MySqlReservaRepository($conn),
            );

            $useCase->execute($validado);

            echo json_encode([
                'status' => 'success',
                'message' => 'Reserva actualizada correctamente',
                'data' => ['localizador' => $validado['localizador'] ?? null],
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
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'ENTRADA_INVALIDA' => 'Formato de entrada inválido',
                'SALIDA_INVALIDA' => 'Formato de salida inválido',
                'SALIDA_ANTERIOR_A_ENTRADA'
                    => 'La salida debe ser posterior a la entrada',
                default => 'Datos inválidos',
            };

            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => $msg]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ActualizarReservaAnualController: ' .
                    $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
