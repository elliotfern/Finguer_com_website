<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario;

use App\Application\Shared\Schema\SchemaValidationException;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class ActualizarUsuarioController
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

        try {
            $uuidStr = trim((string) ($data['uuid'] ?? ''));
            if ($uuidStr === '') {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'UUID requerido',
                ]);
                exit();
            }
            $uuid = UsuarioUuid::fromString($uuidStr);

            $repo = new MySqlUsuarioRepository(MysqlConnection::get());

            $existente = $repo->findByUuid($uuid);
            if ($existente === null) {
                http_response_code(404);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Usuario no encontrado',
                ]);
                exit();
            }

            $email = Email::fromString($data['email'] ?? '');

            $otro = $repo->findByEmail($email);
            if ($otro !== null && !$otro->uuid()->equals($uuid)) {
                http_response_code(409);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Ya existe un usuario con este email',
                ]);
                exit();
            }

            $plainPass = trim((string) ($data['password'] ?? ''));
            $passwordHash =
                $plainPass !== ''
                    ? password_hash($plainPass, PASSWORD_DEFAULT)
                    : $existente->password();

            $usuarioActualizado = Usuario::fromDatabase(
                uuid: $uuid,
                email: $email,
                estado: $existente->estado(),
                rol: Rol::tryFrom($data['tipo_rol'] ?? '') ?? $existente->rol(),
                locale: Locale::tryFrom($data['locale'] ?? '') ??
                    $existente->locale(),
                password: $passwordHash,
            );
            $repo->save($usuarioActualizado);

            if (!empty($data['nombre'])) {
                $perfilDto = ActualizarPerfilDTO::fromArray($data);
                $perfil = UsuarioFactory::crearPerfil($uuid, $perfilDto);
                $repo->savePerfil($perfil);
            }

            echo json_encode([
                'status' => 'success',
                'usuario_uuid_hex' => str_replace('-', '', $uuidStr),
                'message' => 'Usuario actualizado correctamente',
            ]);
        } catch (SchemaValidationException $e) {
            http_response_code(422);
            echo json_encode([
                'status' => 'error',
                'message' => 'Datos inválidos',
                'errors' => $e->toApiArray(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ActualizarUsuarioController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
