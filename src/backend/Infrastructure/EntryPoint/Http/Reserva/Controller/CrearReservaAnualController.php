<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Reserva\Controller;

use App\Application\Reserva\Schema\ReservaSchema;
use App\Application\Reserva\UseCase\CrearReservaAnualUseCase;
use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Shared\Schema\SchemaValidationException;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Reserva\LocalizadorGenerator;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class CrearReservaAnualController
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
                ReservaSchema::crearAnual(),
            );

            $conn = MysqlConnection::get();
            $useCase = new CrearReservaAnualUseCase(
                new MySqlUsuarioRepository($conn),
                new MySqlReservaRepository($conn),
                new LocalizadorGenerator($conn),
            );

            $reserva = $useCase->execute($validado);

            echo json_encode([
                'status' => 'success',
                'message' => 'Reserva creada correctamente',
                'data' => ['localizador' => $reserva->localizador()],
            ]);
        } catch (SchemaValidationException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\InvalidArgumentException $e) {
            $msg = match ($e->getMessage()) {
                'USUARIO_NO_ENCONTRADO' => 'Usuario no existe',
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
                '[FINGUER] CrearReservaAnualController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
