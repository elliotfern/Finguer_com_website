<?php
global $conn;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;

$jwtSecret = $_ENV['TOKEN'];

if (empty($_POST['email']) || empty($_POST['password'])) {
    // Si no se proporcionan el email o la contraseña
    $response = array(
        "status" => "error",
        "message" => "Email i contrasenya són requerits"
    );
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

$email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);  // Validamos el formato del email
$password = $_POST['password'];

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

$stmt = $conn->prepare(
    "SELECT u.id, u.email, u.password, u.tipoUsuario
    FROM usuaris AS u
    WHERE u.email = :email"
);
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
    $tipoUsuario = $row['tipoUsuario'];

    // Verificar si el tipoUsuario es 1
    if ($tipoUsuario != 1) {
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
?>
