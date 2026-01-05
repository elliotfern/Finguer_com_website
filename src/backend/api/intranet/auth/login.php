<?php
global $conn;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;

$jwtSecret = $_ENV['TOKEN'];

// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permitir acceso desde cualquier origen (opcional, según el caso)
header("Access-Control-Allow-Methods: POST");

// Leer el cuerpo de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verificar que los datos se recibieron correctamente
if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No se enviaron datos válidos.",
    ]);
    exit;
}


if (empty($data['email']) || empty($data['password'])) {
    // Si no se proporcionan el email o la contraseña
    $response = array(
        "status" => "error",
        "message" => "Email i contrasenya són requerits"
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);  // Validamos el formato del email
$password = $data['password'];

if ($email === false) {
    // Si el email no es válido
    $response = array(
        "status" => "error",
        "message" => "Email no vàlid."
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$query = "SELECT u.id, u.email, u.password, u.tipo_rol
    FROM usuarios AS u
    WHERE u.email = :email";

// Preparar la consulta
/** @var PDO $conn */
$stmt = $conn->prepare($query);

$stmt->execute(['email' => $email]);

if ($stmt->rowCount() === 0) {
    // Si no se encuentra el usuario
    $response = array(
        "status" => "error",
        "message" => "Compte d'usuari no habilitat."
    );
} else {
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $hash = $row['password'];
    $id = $row['id'];
    $tipoUsuario = $row['tipo_rol'];

    // Verificar si el tipoUsuario es 1
    if ($tipoUsuario != 'admin') {
        // Si el tipoUsuario no es 1
        $response = array(
            "status" => "error",
            "message" => "Accés no autoritzat."
        );
    } elseif (password_verify($password, $hash)) {
        // Contraseña correcta, creamos el JWT y las cookies
        $key = $jwtSecret;
        $algorithm = "HS256";
        $payload = array(
            "user_id" => $id,
            "email" => $row['email'],
            "kid" => "key_api",
            "exp" => time() + (10 * 24 * 60 * 60) // Token expira en 10 días
        );

        $jwt = JWT::encode($payload, $key, $algorithm);

        $expiration = time() + (10 * 24 * 60 * 60); // 10 días
        setcookie('user_id', $id, $expiration, '/', '', true, true);

        // Establecer la cookie token en el servidor
        setcookie('token', $jwt, $expiration, '/', '', true, true);  // Expira en 10 días, Secure y HttpOnly activados

        setcookie('user_type', $tipoUsuario, $expiration, '/', '', true, true);  // Expira en 10 días, Secure y HttpOnly activados

        $response = array(
            "status" => "success",
            "message" => "Accés autoritzat, accedint a la intranet..."
        );
    } else {
        // Si la contraseña es incorrecta
        $response = array(
            "status" => "error",
            "message" => "Contrasenya incorrecta."
        );
    }
}

header('Content-Type: application/json');
echo json_encode($response);
