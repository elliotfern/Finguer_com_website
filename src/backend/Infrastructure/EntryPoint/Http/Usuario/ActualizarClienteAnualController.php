<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario;

use App\Application\Shared\Schema\SchemaValidationException;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use App\Application\Usuario\Factory\UsuarioFactory;

final class ActualizarClienteAnualController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: PUT, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
            exit();
        }

        requireAuthTokenCookie();

        $data = json_decode(file_get_contents('php://input'), true);

        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'JSON inválido',
            ]);
            exit();
        }

        $uuidStr = trim((string) ($data['uuid'] ?? ''));
        if ($uuidStr === '') {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'UUID requerido',
            ]);
            exit();
        }
        if (empty($data['matricula'])) {
            http_response_code(400);
            echo json_encode([
                'status' => 'error',
                'message' => 'La matrícula es obligatoria',
            ]);
            exit();
        }

        $uuid = UsuarioUuid::fromString($uuidStr);
        $usuarioRepo = new MySqlUsuarioRepository(MysqlConnection::get());

        $usuarioExistente = $usuarioRepo->findByUuid($uuid);
        if ($usuarioExistente === null) {
            http_response_code(404);
            echo json_encode([
                'status' => 'error',
                'message' => 'Usuario no encontrado',
            ]);
            exit();
        }

        $conn = MysqlConnection::get();
        $conn->beginTransaction();
        try {
            // 1. Usuario
            $usuarioActualizado = Usuario::fromDatabase(
                uuid: $uuid,
                email: Email::fromString(
                    $data['email'] ?? $usuarioExistente->email()->value(),
                ),
                estado: $usuarioExistente->estado(),
                rol: $usuarioExistente->rol(),
                locale: Locale::tryFrom($data['locale'] ?? '') ??
                    $usuarioExistente->locale(),
                password: $usuarioExistente->password(),
            );
            $usuarioRepo->save($usuarioActualizado);

            // 2. Perfil
            $perfilDto = ActualizarPerfilDTO::fromArray($data);
            $perfil = UsuarioFactory::crearPerfil($uuid, $perfilDto);
            $usuarioRepo->savePerfil($perfil);

            // 3. Abono: actualizar si existe, crear si no
            $abonoRepo = new MySqlAbonoRepository($conn);
            $abonoExistente = $abonoRepo->findByUsuarioUuid($uuid)[0] ?? null;

            $abono = Abono::fromDatabase(
                id: $abonoExistente?->id() ?? UsuarioUuid::generate(),
                usuarioUuid: $uuid,
                estado: AbonoEstado::tryFrom($data['estado'] ?? '') ??
                    ($abonoExistente?->estado() ?? AbonoEstado::Activo),
                fechaInicio: new \DateTimeImmutable(
                    $data['fecha_inicio'] ??
                        $abonoExistente?->fechaInicio()->format('Y-m-d'),
                ),
                fechaFin: new \DateTimeImmutable(
                    $data['fecha_fin'] ??
                        $abonoExistente?->fechaFin()->format('Y-m-d'),
                ),
                limiteReservas: (int) ($data['limite_reservas'] ??
                    ($abonoExistente?->limiteReservas() ?? 10)),
                matricula: Matricula::fromString($data['matricula']),
                vehiculo: $data['vehiculo'] ?? null,
                observaciones: $data['observaciones'] ?? null,
            );
            $abonoRepo->save($abono);

            $conn->commit();

            echo json_encode([
                'status' => 'success',
                'usuario_uuid_hex' => str_replace('-', '', $uuidStr),
                'message' => 'Cliente anual actualizado correctamente',
            ]);
        } catch (SchemaValidationException $e) {
            $conn->rollBack();
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\Throwable $e) {
            $conn->rollBack();
            http_response_code(500);
            error_log(
                '[FINGUER] ActualizarClienteAnualController: ' .
                    $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
