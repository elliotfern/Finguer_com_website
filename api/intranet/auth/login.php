<?php
global $conn;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;

$jwtSecret = $_ENV['TOKEN'];

if (isset($_POST['email']) && isset($_POST['password'])) {
    $email = filter_var($_POST['email']);
    $password = $_POST['password'];

    if ($email === false) {
        $response = array(
            "status" => "error",
            "message" => "Email no válido."
        );
        header('Content-Type: application/json');
        echo json_encode($response);
        exit;
    }

    $stmt = $conn->prepare(
    "SELECT u.id, u.email, u.password
    FROM usuaris AS u
    WHERE u.email = :email");
    $stmt->execute(['email' => $email]);

    if ($stmt->rowCount() === 0) {
        $response = array(
            "status" => "error",
            "message" => "Cuenta no encontrada o no habilitada."
        );
    } else {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $hash = $row['password'];
        $id = $row['id'];
        if (password_verify($password, $hash)) {
            $_SESSION['user']['id'] = $id;
            $_SESSION['user']['email'] = $row['email'];

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
            setcookie('user_id', $id, $expiration, '/', '', false, true);
            
            // Establecer la cookie con el token
            setcookie('token', $jwt, $expiration, '/', '', false, true);

            $response = array(
                "token" => $jwt,
                "status" => "success"
            );
        } else {
            $response = array(
                "status" => "error",
                "message" => "Contraseña incorrecta."
            );
        }
    }

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    $response = array(
        "status" => "error",
        "message" => "Email o contraseña no proporcionados."
    );
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
