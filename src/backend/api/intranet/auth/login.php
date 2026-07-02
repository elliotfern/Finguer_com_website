<?php

declare(strict_types=1);

use App\Application\Usuario\UseCase\LoginUserUseCase;
use App\Application\Shared\Exception\AuthException;
use App\Application\Usuario\Security\BcryptPasswordVerifier;
use App\Infrastructure\Http\AuthCookieService;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use App\Infrastructure\Security\JwtTokenService;

header('Content-Type: application/json; charset=utf-8');

// --------------------
// CORS
// --------------------
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
$allowedOrigins = ['https://finguer.com', 'http://finguer.local'];

if ($origin && in_array($origin, $allowedOrigins, true)) {
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

// --------------------
// Body
// --------------------
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

// --------------------
// Dependency wiring (SIMPLE Y DIRECTO)
// --------------------

// $conn viene del bootstrap legacy
$useCase = new LoginUserUseCase(
    new MySqlUsuarioRepository($conn),
    new BcryptPasswordVerifier(),
    new JwtTokenService($_ENV['TOKEN']),
    new AuthCookieService(),
);

// --------------------
// Execution
// --------------------
try {
    $result = $useCase->execute($email, $password);

    echo json_encode($result);
    exit();
} catch (AuthException $e) {
    http_response_code(401);

    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage(),
    ]);
    exit();
} catch (Throwable $e) {
    http_response_code(500);

    echo json_encode([
        'status' => 'error',
        'message' => 'Internal server error',
    ]);
    exit();
}
