<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\UseCase\CancelarReservaUseCase;
use App\Domain\Reserva\Exception\EstadoConflictException;
use App\Domain\Reserva\Exception\InvalidTransitionException;
use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlVerificadorFactura;

final class CancelarReservaController
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
        $id = 0;
        if (isset($input['id'])) {
            $id = (int) $input['id'];
        } elseif (isset($input['reserva_id'])) {
            $id = (int) $input['reserva_id'];
        }

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
            $useCase = new CancelarReservaUseCase(
                new MySqlReservaRepository($conn),
                new MySqlVerificadorFactura($conn),
            );
            $useCase->execute($id);

            echo json_encode([
                'status' => 'success',
                'message' => 'Reserva cancelada correctamente',
                'data' => ['id' => $id],
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
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] CancelarReservaController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
