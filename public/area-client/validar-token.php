<?php

use Dotenv\Dotenv;
use Firebase\JWT\JWT;

$jwtSecret = $_ENV['TOKEN'];

// Asegúrate de que la variable $conn esté definida correctamente
global $conn;

// Obtener el token desde la URL
if (empty($_GET['token'])) {
    die('Token no proporcionado.');
}

$token = $_GET['token'];

// Verificar si el token existe y no ha expirado
$stmt = $conn->prepare("SELECT t.userId, t.created_at, u.email FROM epgylzqu_finguer.usuaris_tokens AS t
INNER JOIN epgylzqu_finguer.usuaris AS u ON t.userId = u.id
WHERE token = :token");
$stmt->execute(['token' => $token]);

if ($stmt->rowCount() === 0) {
    die('Token no válido o expirado.');
}

$row = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificar si el token ha expirado
$createdAt = strtotime($row['created_at']); // El timestamp de la creación del token
$validUntil = $createdAt + 2 * 60 * 60; // Añadimos 2 horas de validez

// Si el token ha expirado
if ($validUntil < time()) {
    die('Token expirado.');
}

// Si el token es válido, establecer la cookie y redirigir
$userId = $row['userId'];
$email = $row['email'];

// Contraseña correcta, creamos el JWT y las cookies
$key = $jwtSecret;
$algorithm = "HS256";
$payload = array(
    "user_id" => $userId,
    "email" => $row['email'],
    "kid" => "key_api",
    "exp" => time() + (2 * 60 * 60)
);

$jwt = JWT::encode($payload, $key, $algorithm);

// Asegurarse de que la cookie no sea establecida después de que haya habido salida (como echo)
if (!headers_sent()) {
    setcookie('user_id', $userId, $validUntil, '/', '', true, true);
    setcookie('acceso', "si", $validUntil, '/', '', true, true);
    setcookie('token', $jwt, $validUntil, '/', '', true, true);
    setcookie('email', $email, $validUntil, '/', '', true, true); 

    // Redirigir al área privada
    //header('Location: /area-cliente/reservas');
    echo '<form id="redirectForm" method="POST" action="/area-cliente/reservas">';
    echo '<input type="hidden" name="user_id" value="' . htmlspecialchars($userId) . '">';
    echo '</form>';
    echo '<script>document.getElementById("redirectForm").submit();</script>';
    exit();

} else {
    // En caso de que ya se hayan enviado cabeceras
    die('No se puede establecer la cookie. Las cabeceras ya han sido enviadas.');
}
?>
