<?php

// URL
// https://finguer.com/control/test/?reserva_id=6468

$idReserva = isset($_GET['reserva_id']) ? (int)$_GET['reserva_id'] : 0;

if ($idReserva <= 0) {
    echo "Falta parametro reserva_id";
    exit;
}

global $conn;

try {
    $conn->beginTransaction();

    // 1) Leer totales
    $sql = "
        SELECT total_calculado
        FROM parking_reservas
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

    // 2) (Opcional) crear pago simulado y marcar reserva pagada
    /*
    $pedidoFake = 'TEST-' . time();

    $sqlPago = "
        INSERT INTO pagos
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

    $sqlUpd = "
        UPDATE parking_reservas
        SET estado = 'pagada'
        WHERE id = :id
    ";
    $stmtUpd = $conn->prepare($sqlUpd);
    $stmtUpd->execute([':id' => $idReserva]);
    */

    // 3) Crear factura dentro de la MISMA transacción
    $facturaId = crearFacturaParaReserva($conn, $idReserva, 'test_script');

    if ($facturaId === null) {
        throw new Exception('No se pudo generar la factura para la reserva ' . $idReserva);
    }

    // 4) Si todo OK, commit general
    $conn->commit();

    echo "OK: factura $facturaId creada.";
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "ERROR: " . $e->getMessage();
}
