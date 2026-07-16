<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Application\Shared\Exception\AuthException;
use App\Application\Usuario\Security\BcryptPasswordVerifier;
use App\Application\Usuario\UseCase\LoginUserUseCase;
use App\Infrastructure\Http\AuthCookieService;
use App\Infrastructure\Persistence\MySql\MysqlConnection;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use App\Infrastructure\Security\JwtTokenService;

final class LoginController
{
    private const ALLOWED_ORIGINS = [
        'https://finguer.com',
        'http://finguer.local',
    ];

    public static function handle(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        if ($origin && in_array($origin, self::ALLOWED_ORIGINS, true)) {
            header("Access-Control-Allow-Origin: $origin");
            header('Access-Control-Allow-Credentials: true');
            header('Vary: Origin');
        }

        if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
            header('Access-Control-Allow-Methods: POST, OPTIONS');
            header('Access-Control-Allow-Headers: Content-Type');
            http_response_code(204);
            exit();
        }

        $data = json_decode(file_get_contents('php://input'), true);

        if (empty($data['email']) || empty($data['password'])) {
            echo json_encode([
                'status' => 'error',
                'message' => 'Email i contrasenya són requerits',
            ]);
            exit();
        }

        $email = (string) $data['email'];
        $password = (string) $data['password'];

        try {
            $conn = MysqlConnection::get();
            $useCase = new LoginUserUseCase(
                new MySqlUsuarioRepository($conn),
                new BcryptPasswordVerifier(),
                new JwtTokenService($_ENV['TOKEN']),
                new AuthCookieService(),
            );

            $result = $useCase->execute($email, $password);

            echo json_encode($result);
        } catch (AuthException $e) {
            http_response_code(401);
            echo json_encode([
                'status' => 'error',
                'message' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            error_log('[FINGUER] LoginController: ' . $e->getMessage());
            echo json_encode([
                'status' => 'error',
                'message' => 'Internal server error',
            ]);
        }
    }
}
