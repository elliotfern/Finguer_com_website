<?php
$token = $_ENV['MERCHANTCODE'];
$token2 = $_ENV['KEY'];
$token3 = $_ENV['TERMINAL'];
$url_Ok = $_ENV['URLOK'];
$url_Ko = $_ENV['URLKO'];

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


// Validar si la variable costTotal existe y no está vacía
if (isset($data['costTotal']) && !empty($data['costTotal'])) {
    $costTotal = $data['costTotal']; // Recuperar el valor
}

// OBJECTE REDSYS
$miObj = new RedsysAPI;

// Valores de entrada
$fuc = $token;
$terminal = $token3;
$moneda = "978";
$trans = "0";
$url = "";
$urlOK = $url_Ok;
$urlKO = $url_Ko;
$idReserva = date("mdHis");
$amount = round($costTotal * 100);
$kc = $token2; //Clave recuperada de CANALES

// Se Rellenan los campos
$miObj->setParameter("DS_MERCHANT_AMOUNT", $amount);
$miObj->setParameter("DS_MERCHANT_ORDER", $idReserva);
$miObj->setParameter("DS_MERCHANT_MERCHANTCODE", $fuc);
$miObj->setParameter("DS_MERCHANT_CURRENCY", $moneda);
$miObj->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $trans);
$miObj->setParameter("DS_MERCHANT_TERMINAL", $terminal);
$miObj->setParameter("DS_MERCHANT_MERCHANTURL", $url);
$miObj->setParameter("DS_MERCHANT_URLOK", $urlOK);
$miObj->setParameter("DS_MERCHANT_URLKO", $urlKO);

// Se generan los parámetros de la petición
$params = $miObj->createMerchantParameters();
$signature = $miObj->createMerchantSignature($kc);

$response = [
    'status' => 'success',
    'params' => $params,
    'signature' => $signature,
    'idReserva' => $idReserva
];

header("Content-Type: application/json");
// Enviar la respuesta en formato JSON
echo json_encode($response);
