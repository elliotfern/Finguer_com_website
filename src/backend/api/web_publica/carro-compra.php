<?php
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

// validar camps obligatoris

// Ahora extraemos los datos del objeto JSON recibido
$session = data_input($data["session"]);
$precioTotal = data_input($data["precioTotal"]);
$costeSeguro = data_input($data["costeSeguro"]);
$precioReserva = data_input($data["precioReserva"]);
$costeIva = data_input($data["costeIva"]);
$precioSubtotal = data_input($data["precioSubtotal"]);
$costoLimpiezaSinIva = data_input($data["costoLimpiezaSinIva"]);
$fechaEntrada = data_input($data["fechaEntrada"]);
$fechaSalida = data_input($data["fechaSalida"]);
$horaEntrada = data_input($data["horaEntrada"]);
$horaSalida = data_input($data["horaSalida"]);
$limpieza = data_input($data["limpieza"]);
$tipoReserva = data_input($data["tipoReserva"]);
$diasReserva = data_input($data["diasReserva"]);
$seguroCancelacion = data_input($data["seguroCancelacion"]);


global $conn;
$sql = "INSERT INTO carro_compra (session, precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, fechaEntrada, fechaSalida, horaEntrada, horaSalida, limpieza, tipoReserva, diasReserva, seguroCancelacion)
        VALUES (:session, :precioTotal, :costeSeguro, :precioReserva, :costeIva, :precioSubtotal, :costoLimpiezaSinIva, :fechaEntrada, :fechaSalida, :horaEntrada, :horaSalida, :limpieza, :tipoReserva, :diasReserva, :seguroCancelacion)";;

/** @var PDO $conn */
$stmt = $conn->prepare($sql);
$stmt->bindParam(':session', $session, PDO::PARAM_STR);
$stmt->bindParam(':precioTotal', $precioTotal, PDO::PARAM_STR);
$stmt->bindParam(':costeSeguro', $costeSeguro, PDO::PARAM_STR);
$stmt->bindParam(':precioReserva', $precioReserva, PDO::PARAM_STR);
$stmt->bindParam(':costeIva', $costeIva, PDO::PARAM_STR);
$stmt->bindParam(':precioSubtotal', $precioSubtotal, PDO::PARAM_STR);
$stmt->bindParam(':costoLimpiezaSinIva', $costoLimpiezaSinIva, PDO::PARAM_STR);
$stmt->bindParam(':fechaEntrada', $fechaEntrada, PDO::PARAM_STR);
$stmt->bindParam(':fechaSalida', $fechaSalida, PDO::PARAM_STR);
$stmt->bindParam(':horaEntrada', $horaEntrada, PDO::PARAM_STR);
$stmt->bindParam(':horaSalida', $horaSalida, PDO::PARAM_STR);
$stmt->bindParam(':limpieza', $limpieza, PDO::PARAM_STR);
$stmt->bindParam(':tipoReserva', $tipoReserva, PDO::PARAM_STR);
$stmt->bindParam(':diasReserva', $diasReserva, PDO::PARAM_STR);
$stmt->bindParam(':seguroCancelacion', $seguroCancelacion, PDO::PARAM_STR);

if ($stmt->execute()) {
    // response output
    $response['status'] = "success";

    header("Content-Type: application/json");
    echo json_encode($response);
} else {
    // response output - data error
    $response['status'] = 'error';

    header("Content-Type: application/json");
    echo json_encode($response);
}
