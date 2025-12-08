<?php

$idReserva = isset($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : 0;

if ($idReserva <= 0) {
    echo "Falta parametro reserva_id";
    exit;
}

global $conn;

try {
    $conn->beginTransaction();

    // 1) Leer totales de la reserva
    $sql = "
        SELECT total_calculado
        FROM epgylzqu_parking_finguer_v2.parking_reservas
        WHERE id = :id
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idReserva, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        throw new Exception("Reserva no encontrada");
    }

    $total = (float)$row['total_calculado'];

    // 2) Crear un pago simulado (como si Redsys hubiese dicho OK)
    $pedidoFake = 'TEST-' . time();

    $sqlPago = "
        INSERT INTO epgylzqu_parking_finguer_v2.pagos
        (reserva_id, factura_id, fecha, metodo, importe, estado, pasarela, pedido_pasarela)
        VALUES
        (:reserva_id, NULL, NOW(), 'tarjeta', :importe, 'confirmado', 'REDSYS_TEST', :pedido)
    ";
    $stmtPago = $conn->prepare($sqlPago);
    $stmtPago->execute([
        ':reserva_id' => $idReserva,
        ':importe'    => $total,
        ':pedido'     => $pedidoFake,
    ]);

    // 3) Marcar reserva como pagada
    $sqlUpd = "
        UPDATE epgylzqu_parking_finguer_v2.parking_reservas
        SET estado = 'pagada'
        WHERE id = :id
    ";
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->execute([':id' => $idReserva]);

    // 4) Crear factura REAL usando la función que definimos antes
    $facturaId = crearFacturaParaReserva($conn, $idReserva);
    if (!$facturaId) {
        throw new Exception("No se pudo crear la factura");
    }

    $conn->commit();

    // 5) Enviar emails
    enviarConfirmacio($idReserva);
    enviarFactura($facturaId);

    echo "OK: pago simulado, factura $facturaId creada y emails enviados.";
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "ERROR: " . $e->getMessage();
}
