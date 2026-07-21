<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\UseCase\MarcarVehiculoDentroUseCase;
use App\Application\Reserva\UseCase\MarcarVehiculoSalidoUseCase;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Exception\EstadoConflictException;
use App\Domain\Reserva\Exception\InvalidTransitionException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;

final class ActualizarEstadoVehiculoController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
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

        $input = json_decode(file_get_contents('php://input'), true);
        $id = isset($input['id']) ? (int) $input['id'] : 0;
        $nuevoEstadoRaw = isset($input['estado_vehiculo'])
            ? (string) $input['estado_vehiculo']
            : '';

        if ($id <= 0) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetro id inválido',
            ]);
            exit();
        }

        $nuevoEstado = EstadoVehiculo::tryFrom($nuevoEstadoRaw);
        if ($nuevoEstado === null) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'Parámetro estado_vehiculo inválido',
                'allowed' => array_map(
                    fn($c) => $c->value,
                    EstadoVehiculo::cases(),
                ),
            ]);
            exit();
        }

        try {
            $conn = MysqlConnection::get();
            $repo = new MySqlReservaRepository($conn);

            match ($nuevoEstado) {
                EstadoVehiculo::Dentro => new MarcarVehiculoDentroUseCase(
                    $repo,
                )->execute($id),
                EstadoVehiculo::Salido => new MarcarVehiculoSalidoUseCase(
                    $repo,
                )->execute($id),
                EstadoVehiculo::PendienteEntrada
                    => throw new \InvalidArgumentException(
                    'No se puede volver manualmente a pendiente_entrada.',
                ),
            };

            echo json_encode([
                'status' => 'success',
                'message' => 'Estado actualizado correctamente',
                'data' => [
                    'id' => $id,
                    'estado_vehiculo' => $nuevoEstado->value,
                ],
            ]);
        } catch (ReservaNotFoundException $e) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Reserva no encontrada',
            ]);
        } catch (InvalidTransitionException $e) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' => 'Transición de estado no permitida',
                'data' => [
                    'id' => $e->id,
                    'from' => $e->from,
                    'to' => $e->to,
                    'allowed_to' => $e->allowedTo,
                ],
            ]);
        } catch (EstadoConflictException $e) {
            http_response_code(409);
            echo json_encode([
                'status' => 'error',
                'message' =>
                    'Conflicto: el estado ha cambiado, recarga la tabla',
                'data' => [
                    'id' => $e->id,
                    'expected' => $e->expected,
                    'current' => $e->current,
                    'requested' => $e->requested,
                ],
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
                '[FINGUER] ActualizarEstadoVehiculoController: ' .
                    $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
