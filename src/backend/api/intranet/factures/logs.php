<?php
// scripts/rellenar_logs_facturas.php

declare(strict_types=1);

global $conn;

/** @var PDO $conn */

// 1) Obtener todas las facturas existentes
$sqlFacturas = "
    SELECT
        f.id,
        f.fecha_emision,
        f.subtotal,
        f.impuesto_total,
        f.total
    FROM epgylzqu_parking_finguer_v2.facturas f
    ORDER BY f.id ASC
";
$stmt = $conn->prepare($sqlFacturas);
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Facturas encontradas: " . count($facturas) . PHP_EOL;

// 2) Insertar logs uno a uno
$sqlInsertLog = "
    INSERT INTO epgylzqu_parking_finguer_v2.facturas_logs
    (factura_id, usuario_id, accion, detalles_json, created_at)
    VALUES (:factura_id, NULL, :accion, :detalles_json, :created_at)
";
$stmtInsert = $conn->prepare($sqlInsertLog);

$accion = 'creacion_automatica_redsys';
$insertadas = 0;

foreach ($facturas as $f) {
    $facturaId     = (int)$f['id'];
    $fechaEmision  = $f['fecha_emision'] ?? date('Y-m-d H:i:s');
    $subtotal      = $f['subtotal'] !== null ? (float)$f['subtotal'] : 0.0;
    $impuestoTotal = $f['impuesto_total'] !== null ? (float)$f['impuesto_total'] : 0.0;
    $total         = $f['total'] !== null ? (float)$f['total'] : 0.0;

    // Detalles en JSON (si la columna es JSON o TEXT da igual)
    $detalles = [
        'nota'           => 'Factura creada automàticament per Redsys abans d\'implantar el sistema de logs',
        'origen'         => 'redsys',
        'subtotal'       => $subtotal,
        'impuesto_total' => $impuestoTotal,
        'total'          => $total,
        'fecha_emision'  => $fechaEmision,
    ];
    $detallesJson = json_encode($detalles, JSON_UNESCAPED_UNICODE);

    // Por si acaso, log de consola
    echo "Insertando log para factura ID={$facturaId}" . PHP_EOL;

    $stmtInsert->bindValue(':factura_id', $facturaId, PDO::PARAM_INT);
    $stmtInsert->bindValue(':accion', $accion, PDO::PARAM_STR);
    $stmtInsert->bindValue(':detalles_json', $detallesJson, PDO::PARAM_STR);
    $stmtInsert->bindValue(':created_at', $fechaEmision, PDO::PARAM_STR);

    if (!$stmtInsert->execute()) {
        $errorInfo = $stmtInsert->errorInfo();
        echo "ERROR insertando log para factura {$facturaId}: " . implode(' | ', $errorInfo) . PHP_EOL;
    } else {
        $insertadas++;
    }
}

echo "Logs insertados: {$insertadas}" . PHP_EOL;
