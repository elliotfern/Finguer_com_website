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

// OBJECTE REDSYS BIZUM
$miObj2 = new RedsysAPI;

// Se Rellenan los campos
$payment = "z";

//Datos de configuración
$version = "HMAC_SHA256_V1";
$kc = $token2; //Clave recuperada de CANALES

$miObj2->setParameter("DS_MERCHANT_PAYMETHODS", $payment);
$miObj2->setParameter("DS_MERCHANT_AMOUNT", $amount);
$miObj2->setParameter("DS_MERCHANT_ORDER", $idReserva);
$miObj2->setParameter("DS_MERCHANT_MERCHANTCODE", $fuc);
$miObj2->setParameter("DS_MERCHANT_CURRENCY", $moneda);
$miObj2->setParameter("DS_MERCHANT_TRANSACTIONTYPE", $trans);
$miObj2->setParameter("DS_MERCHANT_TERMINAL", $terminal);
$miObj2->setParameter("DS_MERCHANT_MERCHANTURL", $url);
$miObj2->setParameter("DS_MERCHANT_URLOK", $urlOK);
$miObj2->setParameter("DS_MERCHANT_URLKO", $urlKO);

$params2 = $miObj2->createMerchantParameters();
$signature2 = $miObj2->createMerchantSignature($kc);

$response = [
    'status' => 'success',
    'params' => $params2,
    'signature' => $signature2,
    'idReserva' => $idReserva
];

header("Content-Type: application/json");
// Enviar la respuesta en formato JSON
echo json_encode($response);
