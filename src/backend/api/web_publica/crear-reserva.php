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
$hasError = false;

// ------------------------
// VALIDACIONES DE CAMPOS
// ------------------------

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

// ------------------------
// EXTRAER Y NORMALIZAR DATOS
// ------------------------


$vehiculo        = data_input($data["vehiculo"]);
$matricula       = data_input($data["matricula"]);
$vuelo           = data_input($data["vuelo"]);
$numeroPersonas  = (int) data_input($data["numeroPersonas"]);

$idClient        = isset($data["idClient"]) ? data_input($data["idClient"], ENT_NOQUOTES) : null;
$idReserva       = isset($data["idReserva"]) ? data_input($data["idReserva"], ENT_NOQUOTES) : null; // localizador
$tipo            = isset($data["tipo"]) ? data_input($data["tipo"], ENT_NOQUOTES) : null;
$horaEntrada     = isset($data["horaEntrada"]) ? data_input($data["horaEntrada"], ENT_NOQUOTES) : null;
$horaSalida      = isset($data["horaSalida"]) ? data_input($data["horaSalida"], ENT_NOQUOTES) : null;
$limpieza        = isset($data["limpieza"]) ? $data["limpieza"] : null; // 0/1/2/3
$diaEntrada2     = isset($data["diaEntrada"]) ? $data["diaEntrada"] : null;
$diaSalida2      = isset($data["diaSalida"]) ? $data["diaSalida"] : null;
$seguroCancelacion = isset($data["cancelacion"]) ? (int)$data["cancelacion"] : 0;

// Costes (los seguimos leyendo del frontend)
$costeSubTotal   = isset($data["costeSubTotal"])  ? (float)$data["costeSubTotal"]  : 0;
$costeIva        = isset($data["costeIva"])       ? (float)$data["costeIva"]       : 0;
$importe         = isset($data["costeTotal"])     ? (float)$data["costeTotal"]     : 0;

// Costes (los seguimos leyendo del frontend, de momento no se usan)
$costeSeguro     = isset($data["costeSeguro"])    ? (float)$data["costeSeguro"]    : 0;
$costeReserva    = isset($data["costeReserva"])   ? (float)$data["costeReserva"]   : 0;
$costeLimpieza   = isset($data["costeLimpieza"])  ? (float)$data["costeLimpieza"]  : 0;

// checkIn / processed ya no se guardan en la nueva tabla; usamos estados nuevos
//$checkIn      = isset($data["checkIn"]) ? $data["checkIn"] : 5;

// Validar que todos los datos necesarios estén presentes
if (!$idClient || !$idReserva || !$tipo || !$horaEntrada || !$horaSalida || !$vuelo || !$numeroPersonas) {
    echo json_encode([
        "status" => "error",
        "message" => "Datos incompletos."
    ]);
    exit;
}

// ------------------------
// CONVERTIR FECHAS
// ------------------------

$diaEntrada = null;
$diaSalida  = null;

if ($diaEntrada2) {
    $fecha_objeto = DateTime::createFromFormat("d/m/Y", $diaEntrada2);
    if ($fecha_objeto !== false) {
        $diaEntrada = $fecha_objeto->format("Y-m-d");
    }
}

if ($diaSalida2) {
    $fecha_objeto2 = DateTime::createFromFormat("d/m/Y", $diaSalida2);
    if ($fecha_objeto2 !== false) {
        $diaSalida = $fecha_objeto2->format("Y-m-d");
    }
}

if (!$diaEntrada || !$diaSalida) {
    echo json_encode([
        "status" => "error",
        "message" => "Formato de fecha de entrada o salida inválido."
    ]);
    exit;
}

// Montamos DATETIME completos para entrada/salida
// Asumimos horaEntrada/horaSalida en formato HH:MM
$entradaPrevista = $diaEntrada . ' ' . $horaEntrada . ':00';
$salidaPrevista  = $diaSalida  . ' ' . $horaSalida  . ':00';

$fechaReserva = date("Y-m-d H:i:s");

// Mapear tipo string → número
if ($tipo === "finguer_class") {
    $tipoNumber = 1;
} else {
    $tipoNumber = 2; // Gold Finguer
}

// ------------------------
// INSERTAR EN NUEVA BD
// ------------------------

global $conn;

try {
    $conn->beginTransaction();

    // 1) Insert en parking_reservas
    $sqlReserva = "
        INSERT INTO epgylzqu_parking_finguer_v2.parking_reservas
        (
            usuario_id,
            localizador,
            estado,
            estado_vehiculo,
            fecha_reserva,
            entrada_prevista,
            salida_prevista,
            personas,
            tipo,
            vuelo,
            vehiculo,
            matricula,
            subtotal_calculado,
            iva_calculado,
            total_calculado
        ) VALUES (
            :usuario_id,
            :localizador,
            :estado,
            :estado_vehiculo,
            :fecha_reserva,
            :entrada_prevista,
            :salida_prevista,
            :personas,
            :tipo,
            :vuelo,
            :vehiculo,
            :matricula,
            :subtotal_calculado,
            :iva_calculado,
            :total_calculado
        )
    ";

    /** @var PDO $conn */
    $stmt = $conn->prepare($sqlReserva);
    $estadoReserva       = 'pendiente';
    $estadoVehiculo      = 'pendiente_entrada';

    $stmt->bindParam(":usuario_id",      $idClient,        PDO::PARAM_INT);
    $stmt->bindParam(":localizador",     $idReserva,       PDO::PARAM_STR);
    $stmt->bindParam(":estado",          $estadoReserva,   PDO::PARAM_STR);
    $stmt->bindParam(":estado_vehiculo", $estadoVehiculo,  PDO::PARAM_STR);
    $stmt->bindParam(":fecha_reserva",   $fechaReserva,    PDO::PARAM_STR);
    $stmt->bindParam(":entrada_prevista", $entradaPrevista, PDO::PARAM_STR);
    $stmt->bindParam(":salida_prevista", $salidaPrevista,  PDO::PARAM_STR);
    $stmt->bindParam(":personas",        $numeroPersonas,  PDO::PARAM_INT);
    $stmt->bindParam(":tipo",            $tipoNumber,      PDO::PARAM_INT);
    $stmt->bindParam(":vuelo",           $vuelo,           PDO::PARAM_STR);
    $stmt->bindParam(":vehiculo",        $vehiculo,        PDO::PARAM_STR);
    $stmt->bindParam(":matricula",       $matricula,       PDO::PARAM_STR);
    $stmt->bindParam(":subtotal_calculado", $costeSubTotal);
    $stmt->bindParam(":iva_calculado",     $costeIva);
    $stmt->bindParam(":total_calculado",   $importe);

    if (!$stmt->execute()) {
        throw new Exception("Error al insertar la reserva.");
    }

    $reservaId = (int)$conn->lastInsertId();

    // --------------------------------------------------
    // 2) Insertar servicios en parking_reservas_servicios
    // --------------------------------------------------

    // Helper para obtener servicio del catálogo
    $sqlServicio = "
        SELECT id, nombre, iva_percent
        FROM epgylzqu_parking_finguer_v2.parking_servicios_catalogo
        WHERE codigo = :codigo AND activo = 1
        LIMIT 1
    ";
    $stmtServicio = $conn->prepare($sqlServicio);

    // 2.1 Servicio de parking (obligatorio)
    $codigoParking = ($tipoNumber === 1) ? 'RESERVA_FINGUER' : 'RESERVA_FINGUER_GOLD';

    $stmtServicio->execute([':codigo' => $codigoParking]);
    $servParking = $stmtServicio->fetch(PDO::FETCH_ASSOC);

    if (!$servParking) {
        throw new Exception("No se ha encontrado el servicio de parking en el catálogo.");
    }

    $sqlInsertServicio = "
        INSERT INTO epgylzqu_parking_finguer_v2.parking_reservas_servicios
        (
            reserva_id,
            servicio_id,
            descripcion,
            cantidad,
            precio_unitario,
            impuesto_percent,
            total_base,
            total_impuesto,
            total_linea,
            es_coste
        ) VALUES (
            :reserva_id,
            :servicio_id,
            :descripcion,
            :cantidad,
            :precio_unitario,
            :impuesto_percent,
            :total_base,
            :total_impuesto,
            :total_linea,
            :es_coste
        )
    ";

    $stmtInsertServicio = $conn->prepare($sqlInsertServicio);

    // Insert línea de parking
    $cantidad          = 1;
    $precioParking     = $costeReserva;       // base parking
    $ivaParking        = $servParking['iva_percent'];
    $totalBaseParking  = $precioParking;
    $totalImpParking   = 0;                   // Opción A: no calculamos IVA aquí
    $totalLineaParking = $precioParking;
    $esCoste           = 0;

    $stmtInsertServicio->execute([
        ':reserva_id'       => $reservaId,
        ':servicio_id'      => $servParking['id'],
        ':descripcion'      => $servParking['nombre'],
        ':cantidad'         => $cantidad,
        ':precio_unitario'  => $precioParking,
        ':impuesto_percent' => $ivaParking,
        ':total_base'       => $totalBaseParking,
        ':total_impuesto'   => $totalImpParking,
        ':total_linea'      => $totalLineaParking,
        ':es_coste'         => $esCoste,
    ]);

    // 2.2 Servicio de limpieza (si aplica)
    if ($limpieza && $costeLimpieza > 0) {
        switch ((int)$limpieza) {
            case 1:
                $codigoLimpieza = 'LIMPIEZA_EXT';
                break;
            case 2:
                $codigoLimpieza = 'LIMPIEZA_EXT_INT';
                break;
            case 3:
                $codigoLimpieza = 'LIMPIEZA_PRO';
                break;
            default:
                $codigoLimpieza = null;
        }

        if ($codigoLimpieza) {
            $stmtServicio->execute([':codigo' => $codigoLimpieza]);
            $servLimpieza = $stmtServicio->fetch(PDO::FETCH_ASSOC);

            if ($servLimpieza) {
                $precioLimpieza     = $costeLimpieza;
                $ivaLimpieza        = $servLimpieza['iva_percent'];
                $totalBaseLimpieza  = $precioLimpieza;
                $totalImpLimpieza   = 0;
                $totalLineaLimpieza = $precioLimpieza;

                $stmtInsertServicio->execute([
                    ':reserva_id'       => $reservaId,
                    ':servicio_id'      => $servLimpieza['id'],
                    ':descripcion'      => $servLimpieza['nombre'],
                    ':cantidad'         => 1,
                    ':precio_unitario'  => $precioLimpieza,
                    ':impuesto_percent' => $ivaLimpieza,
                    ':total_base'       => $totalBaseLimpieza,
                    ':total_impuesto'   => $totalImpLimpieza,
                    ':total_linea'      => $totalLineaLimpieza,
                    ':es_coste'         => 0,
                ]);
            }
        }
    }

    // 2.3 Servicio de seguro de cancelación (si aplica)
    if ($seguroCancelacion === 1 && $costeSeguro > 0) {
        $stmtServicio->execute([':codigo' => 'SEGURO_CANCELACION']);
        $servSeguro = $stmtServicio->fetch(PDO::FETCH_ASSOC);

        if ($servSeguro) {
            $precioSeguro     = $costeSeguro;
            $ivaSeguro        = $servSeguro['iva_percent'];
            $totalBaseSeguro  = $precioSeguro;
            $totalImpSeguro   = 0;
            $totalLineaSeguro = $precioSeguro;

            $stmtInsertServicio->execute([
                ':reserva_id'       => $reservaId,
                ':servicio_id'      => $servSeguro['id'],
                ':descripcion'      => $servSeguro['nombre'],
                ':cantidad'         => 1,
                ':precio_unitario'  => $precioSeguro,
                ':impuesto_percent' => $ivaSeguro,
                ':total_base'       => $totalBaseSeguro,
                ':total_impuesto'   => $totalImpSeguro,
                ':total_linea'      => $totalLineaSeguro,
                ':es_coste'         => 0,
            ]);
        }
    }

    // Si todo fue bien:
    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Reserva creada correctamente.",
        "data" => [
            "reserva_id"  => $reservaId,
            "localizador" => $idReserva,
            "importe"     => $importe,
            "subTotal"    => $costeSubTotal,
            "iva"         => $costeIva
        ]
    ]);
    exit;
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }

    echo json_encode([
        "status" => "error",
        "message" => "Error al crear la reserva.",
        "error_detail" => $e->getMessage()
    ]);
    exit;
}
