<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// CORS + JSON
header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Accept");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Leer JSON
$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No se enviaron datos válidos."
    ]);
    exit;
}

$errors = [];
$hasError = false;

// =========================
// VALIDACIONES
// =========================

if (!empty($data["website"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Spam detected."
    ]);
    exit;
}

if (!isset($data["form_start"])) {
    echo json_encode([
        "status" => "error",
        "message" => "Missing form_start"
    ]);
    exit;
}

// Nombre
if (empty($data["nombre"])) {
    $errors["nombre"] = "El nombre es obligatorio.";
    $hasError = true;
}

// Email
if (empty($data["email"])) {
    $errors["email"] = "El email es obligatorio.";
    $hasError = true;
} elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Email no válido.";
    $hasError = true;
}

// Teléfono
if (empty($data["telefono"])) {
    $errors["telefono"] = "El teléfono es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[0-9]{9,15}$/", $data["telefono"])) {
    $errors["telefono"] = "Teléfono no válido.";
    $hasError = true;
}

// Privacidad (OBLIGATORIO)
if (empty($data["privacidad"]) || $data["privacidad"] !== true && $data["privacidad"] !== "true" && $data["privacidad"] !== "1") {
    $errors["privacidad"] = "Debes aceptar la política de privacidad.";
    $hasError = true;
}

if (!empty($errors)) {
    echo json_encode([
        "status" => "error",
        "message" => "Errores en los datos enviados.",
        "errors" => $errors
    ]);
    exit;
}

// =========================
// SANITIZACIÓN
// =========================

$nombre   = data_input($data["nombre"]);
$email    = data_input($data["email"]);
$telefono = data_input($data["telefono"]);
$mensaje  = !empty($data["mensaje"]) ? data_input($data["mensaje"]) : null;

$acepta_privacidad = 1;

// =========================
// INFO TÉCNICA
// =========================

$ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'Desconegut';

// =========================
// DB
// =========================

global $conn;
/** @var PDO $conn */

if (!isset($conn) || !($conn instanceof PDO)) {
    echo json_encode([
        "status" => "error",
        "message" => "DB connection not available"
    ]);
    exit;
}

$sql = "INSERT INTO formulario_contacto
        (nombre, telefono, email, mensaje, acepta_privacidad, created_at)
        VALUES
        (:nombre, :telefono, :email, :mensaje, :acepta_privacidad, NOW())";

$stmt = $conn->prepare($sql);

$stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
$stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
$stmt->bindParam(":email", $email, PDO::PARAM_STR);
$stmt->bindParam(":mensaje", $mensaje, PDO::PARAM_STR);
$stmt->bindParam(":acepta_privacidad", $acepta_privacidad, PDO::PARAM_INT);

if ($stmt->execute()) {
    $contactoId = (int)$conn->lastInsertId();
    $emailResult = enviarNotificacionContacto($conn, $contactoId);

if ($emailResult['status'] === 'success') {
    echo json_encode([
        "status" => "success",
        "message" => "Solicitud enviada correctamente. Te contactaremos pronto."
    ]);
} else {
     echo json_encode([
        "status" => "error",
        "message" => "Error en l'enviament email"
    ]);
}

   
} else {

    echo json_encode([
        "status" => "error",
        "message" => "Error en la base de datos."
    ]);
}
