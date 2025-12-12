<?php

declare(strict_types=1);

$token  = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];
$url_Ok = $_ENV['URLOK'];
$url_Ko = $_ENV['URLKO'];

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

// Leer el cuerpo JSON
$raw = file_get_contents("php://input");
$data = json_decode($raw ?: "{}", true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "No se enviaron datos válidos."]);
    exit;
}

// ✅ Ahora recibimos session
$session = trim((string)($data['session'] ?? ''));
if ($session === '') {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Falta session."]);
    exit;
}

// Conexión PDO (como en tu proyecto)
global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "DB connection not available."]);
    exit;
}

// ✅ Leer total desde BD
$stmt = $conn->prepare("
    SELECT total_con_iva
    FROM carro_compra
    WHERE session = :session
    LIMIT 1
");
$stmt->execute([':session' => $session]);
$totalConIva = $stmt->fetchColumn();

if ($totalConIva === false) {
    http_response_code(404);
    echo json_encode(["status" => "error", "message" => "Carrito no encontrado para esta session."]);
    exit;
}

$costTotal = (float)$totalConIva;
if ($costTotal <= 0) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Importe inválido."]);
    exit;
}

// OBJECTE REDSYS
$miObj = new RedsysAPI;

// Valores de entrada
$fuc      = $token;
$terminal = $token3;
$moneda   = "978";
$trans    = "0";
$url      = "";
$urlOK    = $url_Ok;
$urlKO    = $url_Ko;

// ✅ Orden Redsys: mejor asegurar longitud/formato.
// Mantengo tu mdHis (10 dígitos). Si tu banco exige 12, lo ajustamos.
$idReserva = date("mdHis");

// ✅ Importe en céntimos, con redondeo seguro
$amount = (int) round($costTotal * 100);

$kc = $token2;

// Se rellenan los campos
$miObj->setParameter("DS_MERCHANT_AMOUNT", (string)$amount);
$miObj->setParameter("DS_MERCHANT_ORDER", (string)$idReserva);
$miObj->setParameter("DS_MERCHANT_MERCHANTCODE", (string)$fuc);
$miObj->setParameter("DS_MERCHANT_CURRENCY", (string)$moneda);
$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", (string)$trans);
$miObj->setParameter("DS_MERCHANT_TERMINAL", (string)$terminal);
$miObj->setParameter("DS_MERCHANT_MERCHANTURL", (string)$url);
$miObj->setParameter("DS_MERCHANT_URLOK", (string)$urlOK);
$miObj->setParameter("DS_MERCHANT_URLKO", (string)$urlKO);

// Generar parámetros + firma
$params    = $miObj->createMerchantParameters();
$signature = $miObj->createMerchantSignature($kc);

echo json_encode([
    'status'   => 'success',
    'params'   => $params,
    'signature' => $signature,
    'idReserva' => $idReserva,
]);
