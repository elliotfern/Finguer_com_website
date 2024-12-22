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

// Validación para 'vehiculo'
if (empty($data["vehiculo"])) {
    $errors["vehiculo"] = "El modelo del vehículo es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $data["vehiculo"])) {
    $errors["vehiculo"] = "El modelo del vehículo debe contener solo letras, números y espacios.";
    $hasError = true;
}

// Validación para 'matricula'
if (empty($data["matricula"])) {
    $errors["matricula"] = "La matrícula del vehículo es obligatoria.";
    $hasError = true;
}

// Validación para 'vuelo'
if (empty($data["vuelo"])) {
    $errors["vuelo"] = "El número del vuelo es obligatorio.";
    $hasError = true;
}

// Validación para 'numeroPersonas'
if (empty($data["numeroPersonas"])) {
    $errors["numero_personas"] = "El número de acompañantes es obligatorio.";
    $hasError = true;
} elseif (!filter_var($data["numeroPersonas"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 20]])) {
    $errors["numero_personas"] = "El número de acompañantes debe ser un número entre 1 y 20.";
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

// Ahora extraemos los datos del objeto JSON recibido

$vehiculo = data_input($data["vehiculo"]);
$matricula = data_input($data["matricula"]);
$vuelo = data_input($data["vuelo"]);
$numeroPersonas = data_input($data["numeroPersonas"]);

$idClient = isset($data["idClient"]) ? data_input($data["idClient"], ENT_NOQUOTES) : null;
$idReserva = isset($data["idReserva"]) ? data_input($data["idReserva"], ENT_NOQUOTES) : null;
$tipo = isset($data["tipo"]) ? data_input($data["tipo"], ENT_NOQUOTES) : null;
$horaEntrada = isset($data["horaEntrada"]) ? data_input($data["horaEntrada"], ENT_NOQUOTES) : null;
$horaSalida = isset($data["horaSalida"]) ? data_input($data["horaSalida"], ENT_NOQUOTES) : null;
$limpieza = isset($data["limpieza"]) ? $data["limpieza"] : null;
$processed = isset($data["processed"]) ? $data["processed"] : 0;
$diaEntrada2 = isset($data["diaEntrada"]) ? $data["diaEntrada"] : null;
$diaSalida2 = isset($data["diaSalida"]) ? $data["diaSalida"] : null;
$seguroCancelacion = isset($data["cancelacion"]) ? $data["cancelacion"] : null;
$costeReserva = isset($data["costeReserva"]) ? $data["costeReserva"] : 0;
$costeLimpieza = isset($data["costeLimpieza"]) ? $data["costeLimpieza"] : 0;
$costeSubTotal = isset($data["costeSubTotal"]) ? $data["costeSubTotal"] : 0;
$costeIva = isset($data["costeIva"]) ? $data["costeIva"] : 0;
$importe = isset($data["costeTotal"]) ? $data["costeTotal"] : 0;
$costeSeguro = isset($data["costeSeguro"]) ? $data["costeSeguro"] : 0;
$checkIn = isset($data["checkIn"]) ? $data["checkIn"] : 5;

// Validar que todos los datos necesarios estén presentes
if (!$idClient || !$idReserva || !$tipo || !$horaEntrada || !$horaSalida || !$vuelo || !$numeroPersonas) {
    $hasError = true;
    echo json_encode([
        "status" => "error",
        "message" => "Datos incompletos."
    ]);
    exit;
}

// Convertir las fechas si es necesario
if ($diaEntrada2) {
    $fecha_objeto = DateTime::createFromFormat("d/m/Y", $diaEntrada2);
    $diaEntrada = $fecha_objeto->format("Y-m-d");
}

if ($diaSalida2) {
    $fecha_objeto2 = DateTime::createFromFormat("d/m/Y", $diaSalida2);
    $diaSalida = $fecha_objeto2->format("Y-m-d");
}

$fechaReserva = date("Y-m-d H:i:s");

if ($tipo === "finguer_class") {
    $tipoNumber = 1;
} else {
    $tipoNumber = 2;
}

global $conn;
$sql = "INSERT INTO reserves_parking SET idClient=:idClient, idReserva=:idReserva, tipo=:tipo, horaEntrada=:horaEntrada, diaEntrada=:diaEntrada, horaSalida=:horaSalida, diaSalida=:diaSalida, vehiculo=:vehiculo, matricula=:matricula, vuelo=:vuelo, limpieza=:limpieza, processed=:processed, checkIn =:checkIn, fechaReserva=:fechaReserva, seguroCancelacion=:seguroCancelacion, importe=:importe, subTotal=:subTotal, importeIva=:importeIva, costeReserva=:costeReserva, costeSeguro=:costeSeguro, costeLimpieza=:costeLimpieza, numeroPersonas=:numeroPersonas";

/** @var PDO $conn */
$stmt = $conn->prepare($sql);
$stmt->bindParam(":idClient", $idClient, PDO::PARAM_STR);
$stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_STR);
$stmt->bindParam(":tipo", $tipoNumber, PDO::PARAM_INT);
$stmt->bindParam(":horaEntrada", $horaEntrada, PDO::PARAM_STR);
$stmt->bindParam(":diaEntrada", $diaEntrada, PDO::PARAM_STR);
$stmt->bindParam(":horaSalida", $horaSalida, PDO::PARAM_STR);
$stmt->bindParam(":diaSalida", $diaSalida, PDO::PARAM_STR);
$stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
$stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
$stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
$stmt->bindParam(":limpieza", $limpieza, PDO::PARAM_STR);
$stmt->bindParam(":processed", $processed, PDO::PARAM_STR);
$stmt->bindParam(":checkIn", $checkIn, PDO::PARAM_STR);
$stmt->bindParam(":fechaReserva", $fechaReserva, PDO::PARAM_STR);
$stmt->bindParam(":seguroCancelacion", $seguroCancelacion, PDO::PARAM_INT);
$stmt->bindParam(":importe", $importe, PDO::PARAM_STR);
$stmt->bindParam(":subTotal", $costeSubTotal, PDO::PARAM_STR);
$stmt->bindParam(":importeIva", $costeIva, PDO::PARAM_STR);
$stmt->bindParam(":costeReserva", $costeReserva, PDO::PARAM_STR);
$stmt->bindParam(":costeSeguro", $costeSeguro, PDO::PARAM_STR);
$stmt->bindParam(":costeLimpieza", $costeLimpieza, PDO::PARAM_STR);
$stmt->bindParam(":numeroPersonas", $numeroPersonas, PDO::PARAM_INT);

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
