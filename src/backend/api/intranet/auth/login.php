<?php
global $conn;

use Firebase\JWT\JWT;

$jwtSecret = $_ENV['TOKEN'];

header("Content-Type: application/json; charset=utf-8");

// --- CORS (solo los orígenes permitidos) ---
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';

$allowedOrigins = [
    'https://finguer.com',
    'https://dev.finguer.com',
];

// Si viene un Origin válido, lo devolvemos (nunca '*')
if ($origin && in_array($origin, $allowedOrigins, true)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
    header("Vary: Origin");
}

// Preflight
if (($_SERVER['REQUEST_METHOD'] ?? '') === 'OPTIONS') {
    header("Access-Control-Allow-Methods: POST, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type");
    http_response_code(204);
    exit;
}

header("Access-Control-Allow-Methods: POST");

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode(["status" => "error", "message" => "No se enviaron datos válidos."]);
    exit;
}

if (empty($data['email']) || empty($data['password'])) {
    echo json_encode(["status" => "error", "message" => "Email i contrasenya són requerits"]);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);
$password = (string)$data['password'];

if ($email === false) {
    echo json_encode(["status" => "error", "message" => "Email no vàlid."]);
    exit;
}

$query = "
    SELECT u.uuid, u.nombre, u.email, u.password, u.tipo_rol
    FROM usuarios AS u
    WHERE u.email = :email
    LIMIT 1
";

/** @var PDO $conn */
$stmt = $conn->prepare($query);
$stmt->execute(['email' => $email]);

if ($stmt->rowCount() === 0) {
    echo json_encode(["status" => "error", "message" => "Compte d'usuari no habilitat."]);
    exit;
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

$hash = (string)$row['password'];
$tipoUsuario = (string)$row['tipo_rol'];

$rolesPermitidos = ['admin', 'trabajador'];

if (!in_array($tipoUsuario, $rolesPermitidos, true)) {
    echo json_encode(["status" => "error", "message" => "Accés no autoritzat."]);
    exit;
}

if (!password_verify($password, $hash)) {
    echo json_encode(["status" => "error", "message" => "Contrasenya incorrecta."]);
    exit;
}

// uuid viene como BINARY(16) desde PDO
$usuarioUuidBin = $row['uuid'];
if (!is_string($usuarioUuidBin) || strlen($usuarioUuidBin) !== 16) {
    echo json_encode(["status" => "error", "message" => "UUID d'usuari invàlid a la BD."]);
    exit;
}
$usuarioUuidStr = uuid_string_from_bin($usuarioUuidBin);

$now = time();
$expiration = $now + (10 * 24 * 60 * 60); // 10 días (luego lo ajustamos si quieres)

$jti = bin2hex(random_bytes(16)); // id único del token

$payload = [
    "iss"  => "finguer-intranet",
    "iat"  => $now,
    "exp"  => $expiration,
    "jti"  => $jti,

    "sub"  => $usuarioUuidStr,     // ✅ identidad canónica
    "role" => $tipoUsuario,
    "name" => (string)($row['nombre'] ?? ''),
    // "email" => (string)$row['email'], // opcional
];

// 🧹 Limpieza defensiva de tokens antiguos
clearAuthCookies();

$jwt = JWT::encode($payload, $jwtSecret, "HS256");

// ✅ SOLO UNA COOKIE: token
// Mejor usando setcookie con array (PHP 7.3+)
setcookie('token', $jwt, [
    'expires'  => $expiration,
    'path'     => '/',
    'domain'   => '.finguer.com',  // ✅ comparte entre finguer.com y dev.finguer.com
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Lax', // o 'Strict' si la intranet es 100% misma web
]);

echo json_encode([
    "status" => "success",
    "message" => "Accés autoritzat, accedint a la intranet..."
]);
