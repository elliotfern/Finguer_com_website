<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Application\Usuario\UseCase\ListarUsuariosUseCase;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;

final class ListarUsuariosController
{
    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Accept');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(204);
            exit();
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            http_response_code(405);
            echo json_encode([
                'status' => 'error',
                'message' => 'Method not allowed',
            ]);
            exit();
        }

        requireAuthTokenCookie();

        $user = auth_user();
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            http_response_code(403);
            echo json_encode([
                'status' => 'error',
                'message' => 'No autoritzat',
            ]);
            exit();
        }

        try {
            $conn = MysqlConnection::get();
            $useCase = new ListarUsuariosUseCase(
                new MySqlUsuarioRepository($conn),
            );

            $criteria = UsuarioListCriteria::fromRequest($_GET);
            $result = $useCase->execute($criteria);

            echo json_encode([
                'status' => 'success',
                'message' => 'OK',
                'data' => $result->toArray(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log(
                '[FINGUER] ListarUsuariosController: ' . $e->getMessage(),
            );
            echo json_encode([
                'status' => 'error',
                'message' => 'Error interno',
            ]);
        }
    }
}
