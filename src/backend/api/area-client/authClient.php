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
        "errors" => []
    ]);
    exit;
}

$errors = [];

// Validar y sanitizar datos recibidos
$hasError = false;

// Validación para 'vehiculo'
if (empty($data["email"])) {
    $errors["email"] = "El campo email es obligatorio.";
    $hasError = true;
}

// Si hay errores, enviarlos al cliente
if (!empty($errors)) {
    echo json_encode([
        "status" => "error",
        "message" => "Errores en los datos enviados.",
        "errors" => $errors
    ]);
    exit;
}

$email = filter_var($data['email'], FILTER_VALIDATE_EMAIL);  // Validamos el formato del email

if ($email === false) {
    // Si el email no es válido
    $response = array(
        "status" => "error",
        "message" => "Correo electrónico no válido."
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Verifica si el correo electrónico existe en la base de datos

/** @var PDO $conn */
$stmt = $conn->prepare("SELECT id, email FROM usuarios AS u WHERE email = :email");
$stmt->execute(['email' => $email]);

if ($stmt->rowCount() === 0) {
    // Si el correo no existe en la base de datos
    $response = array(
        "status" => "error",
        "message" => "Correo electrónico no registrado."
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
} else {
    // Si el correo electrónico existe, generamos un token único
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $userId = $row['id'];

    // Generar un token único que caduque en un corto período de tiempo
    $token = bin2hex(random_bytes(16)); // Usamos random_bytes para generar un token seguro y único

    // Guardamos el token en la base de datos para verificarlo más tarde
    $expiry = date('Y-m-d H:i:s');
    $stmt = $conn->prepare("INSERT INTO usuarios_tokens  (userId, token, created_at) VALUES (:userId, :token, :created_at)");
    $stmt->execute([
        'userId' => $userId,
        'token' => $token,
        'created_at' => $expiry
    ]);

    // Ahora generamos el enlace que se enviará al usuario
    $link = "https://finguer.com/area-cliente/validar-token?token=" . $token;

    // Enviar el correo electrónico al usuario con el enlace de validación
    $subject = "Acceso a tu área privada";
    $message = "Haz clic en el siguiente enlace para acceder a tu área privada: " . $link;
    $headers = "From: hello@finguer.com";

    // Enviamos el email
    if (mail($email, $subject, $message, $headers)) {
        $response = array(
            "status" => "success",
            "message" => "Se ha enviado un enlace de acceso a tu correo electrónico."
        );
    } else {
        $response = array(
            "status" => "error",
            "message" => "Hubo un problema al enviar el correo."
        );
    }

    header('Content-Type: application/json');
    echo json_encode($response);
}
