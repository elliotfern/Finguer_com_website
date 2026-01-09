<?php

declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode([
        "status" => "error",
        "message" => "No se enviaron datos válidos.",
        "errors"  => []
    ]);
    exit;
}

$errors = [];

// ------------------------
// VALIDACIONES DE CAMPOS (igual que tenías)
// ------------------------
if (empty($data["vehiculo"])) {
    $errors["vehiculo"] = "El modelo del vehículo es obligatorio.";
} elseif (!preg_match("/^[a-zA-Z0-9\s]+$/", $data["vehiculo"])) {
    $errors["vehiculo"] = "El modelo del vehículo debe contener solo letras, números y espacios.";
}

if (empty($data["matricula"])) {
    $errors["matricula"] = "La matrícula del vehículo es obligatoria.";
}

if (empty($data["vuelo"])) {
    $errors["vuelo"] = "El número del vuelo es obligatorio.";
}

if (empty($data["numeroPersonas"])) {
    $errors["numero_personas"] = "El número de acompañantes es obligatorio.";
} elseif (!filter_var($data["numeroPersonas"], FILTER_VALIDATE_INT, ["options" => ["min_range" => 1, "max_range" => 20]])) {
    $errors["numero_personas"] = "El número de acompañantes debe ser un número entre 1 y 20.";
}

// ✅ NUEVO: session obligatoria
$session = trim((string)($data["session"] ?? ''));
if ($session === '') {
    $errors["session"] = "Falta session del carrito.";
}

// usuario_uuid_hex + idReserva obligatorios
$usuarioUuidHex = strtolower(trim((string)($data["usuario_uuid_hex"] ?? '')));
$idReserva      = trim((string)($data["idReserva"] ?? ''));

if ($usuarioUuidHex === '' || !preg_match('/^[0-9a-f]{32}$/', $usuarioUuidHex)) {
    $errors["usuario_uuid_hex"] = "Falta usuario_uuid_hex (32 hex).";
}
if ($idReserva === '') {
    $errors["idReserva"] = "Falta idReserva.";
}

if (!empty($errors)) {
    echo json_encode([
        "status" => "error",
        "message" => "Errores en los datos enviados.",
        "errors" => $errors
    ]);
    exit;
}

// ------------------------
// NORMALIZAR FORM DATA
// ------------------------
$vehiculo       = data_input($data["vehiculo"]);
$matricula      = data_input($data["matricula"]);
$vuelo          = data_input($data["vuelo"]);
$numeroPersonas = (int) data_input($data["numeroPersonas"]);
$fechaReserva   = date("Y-m-d H:i:s");

// ------------------------
// DB
// ------------------------
global $conn;
/** @var PDO $conn */

try {
    // 1) leer carrito desde BD
    $stmtCarro = $conn->prepare("
        SELECT subtotal_sin_iva, iva_total, total_con_iva, lineas_json
        FROM carro_compra
        WHERE session = :session
        LIMIT 1
    ");
    $stmtCarro->execute([':session' => $session]);
    $carro = $stmtCarro->fetch(PDO::FETCH_ASSOC);

    if (!$carro) {
        echo json_encode([
            "status" => "error",
            "message" => "Carrito no encontrado para esta session."
        ]);
        exit;
    }

    $subtotal = (float)$carro['subtotal_sin_iva'];
    $ivaTotal = (float)$carro['iva_total'];
    $total    = (float)$carro['total_con_iva'];

    $snapshot = json_decode((string)$carro['lineas_json'], true);
    if (!is_array($snapshot)) {
        echo json_encode([
            "status" => "error",
            "message" => "Snapshot del carrito inválido."
        ]);
        exit;
    }

    $seleccion = $snapshot['seleccion'] ?? null;
    $lineas    = $snapshot['lineas'] ?? null;
    $diasReserva = $snapshot['diasReserva'] ?? null;

    if (!is_array($seleccion) || !is_array($lineas) || empty($lineas)) {
        echo json_encode([
            "status" => "error",
            "message" => "Snapshot incompleto (seleccion/lineas)."
        ]);
        exit;
    }

    // 2) fechas previstas desde snapshot (YA viene como "YYYY-MM-DD HH:MM:SS")
    $entradaPrevista = (string)($seleccion['fechaEntrada'] ?? '');
    $salidaPrevista  = (string)($seleccion['fechaSalida'] ?? '');

    if ($entradaPrevista === '' || $salidaPrevista === '') {
        echo json_encode([
            "status" => "error",
            "message" => "Faltan fechas en el carrito."
        ]);
        exit;
    }

    // 3) tipo reserva → número (ajusta si en snapshot guardas otros códigos)
    $tipoStr = (string)($seleccion['tipoReserva'] ?? '');
    // ejemplos típicos: "RESERVA_FINGUER" o "RESERVA_FINGUER_GOLD" o "finguer_class"
    $tipoNumber = 1;
    if (stripos($tipoStr, 'GOLD') !== false || $tipoStr === 'gold_finguer') {
        $tipoNumber = 2;
    }

    // ------------------------
    // INSERTAR RESERVA + SERVICIOS
    // ------------------------

    $stmtUser = $conn->prepare("
            SELECT 1
            FROM usuarios
            WHERE uuid = UNHEX(:uuid_hex)
            AND estado = 'activo'
            LIMIT 1
        ");
        
    $stmtUser->execute([':uuid_hex' => $usuarioUuidHex]);
    if (!$stmtUser->fetchColumn()) {
        echo json_encode([
            "status" => "error",
            "message" => "Usuario no encontrado o no activo."
        ]);
        exit;
    }


    $conn->beginTransaction();

    // 4) Insert en parking_reservas (totales desde BD)
    $sqlReserva = "
        INSERT INTO parking_reservas
        (
            usuario_uuid,
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
            total_calculado,
            canal
        ) VALUES (
            :usuario_uuid,
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
            :total_calculado,
            :canal
        )
    ";

    $stmt = $conn->prepare($sqlReserva);

    $estadoReserva  = 'pendiente';
    $estadoVehiculo = 'pendiente_entrada';

    $stmt->execute([
        ':usuario_uuid_hex'   => $usuarioUuidHex,
        ':localizador'       => $idReserva,
        ':estado'            => $estadoReserva,
        ':estado_vehiculo'   => $estadoVehiculo,
        ':fecha_reserva'     => $fechaReserva,
        ':entrada_prevista'  => $entradaPrevista,
        ':salida_prevista'   => $salidaPrevista,
        ':personas'          => $numeroPersonas,
        ':tipo'              => $tipoNumber,
        ':vuelo'             => $vuelo,
        ':vehiculo'          => $vehiculo,
        ':matricula'         => $matricula,
        ':subtotal_calculado' => $subtotal,
        ':iva_calculado'     => $ivaTotal,
        ':total_calculado'   => $total,
        ':canal'             => "1",
    ]);

    $reservaId = (int)$conn->lastInsertId();

    // 5) preparar catálogo servicios (mapeo codigo -> id,nombre,iva)
    $stmtServicio = $conn->prepare("
        SELECT id, nombre, iva_percent
        FROM parking_servicios_catalogo
        WHERE codigo = :codigo AND activo = 1
        LIMIT 1
    ");

    $stmtInsertServicio = $conn->prepare("
        INSERT INTO parking_reservas_servicios
        (
            reserva_id,
            servicio_id,
            descripcion,
            cantidad,
            precio_unitario,
            impuesto_percent,
            total_base,
            total_impuesto,
            total_linea
        ) VALUES (
            :reserva_id,
            :servicio_id,
            :descripcion,
            :cantidad,
            :precio_unitario,
            :impuesto_percent,
            :total_base,
            :total_impuesto,
            :total_linea
        )
    ");

    // 6) Insertar cada línea del snapshot como servicio
    foreach ($lineas as $l) {
        if (!is_array($l)) continue;

        $codigo = (string)($l['codigo'] ?? '');
        if ($codigo === '') continue;

        $stmtServicio->execute([':codigo' => $codigo]);
        $serv = $stmtServicio->fetch(PDO::FETCH_ASSOC);

        if (!$serv) {
            // Si prefieres fallar duro, cambia por throw new Exception(...)
            throw new Exception("Servicio no encontrado en catálogo: " . $codigo);
        }

        $cantidad    = (float)($l['cantidad'] ?? 1);
        $base        = (float)($l['base'] ?? 0);
        $ivaLinea    = (float)($l['iva'] ?? 0);
        $totalLinea  = (float)($l['total'] ?? 0);
        $ivaPercent  = (float)($l['iva_percent'] ?? $serv['iva_percent']);

        // precio_unitario: si cantidad > 0, distribuimos base
        $precioUnit = ($cantidad > 0) ? round($base / $cantidad, 2) : $base;

        $stmtInsertServicio->execute([
            ':reserva_id'       => $reservaId,
            ':servicio_id'      => $serv['id'],
            ':descripcion'      => (string)($l['descripcion'] ?? $serv['nombre']),
            ':cantidad'         => $cantidad,
            ':precio_unitario'  => $precioUnit,
            ':impuesto_percent' => $ivaPercent,
            ':total_base'       => $base,
            ':total_impuesto'   => $ivaLinea,
            ':total_linea'      => $totalLinea,
        ]);
    }

    $conn->commit();

    echo json_encode([
        "status" => "success",
        "message" => "Reserva creada correctamente.",
        "data" => [
            "reserva_id"  => $reservaId,
            "localizador" => $idReserva,
            "importe"     => $total,
            "subTotal"    => $subtotal,
            "iva"         => $ivaTotal,
            "diasReserva" => $diasReserva,
        ]
    ]);
    exit;
} catch (Exception $e) {
    if ($conn && $conn->inTransaction()) $conn->rollBack();

    echo json_encode([
        "status" => "error",
        "message" => "Error al crear la reserva.",
        "error_detail" => $e->getMessage()
    ]);
    exit;
}
