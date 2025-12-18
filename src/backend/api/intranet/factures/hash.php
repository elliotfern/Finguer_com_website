<?php
// scripts/inicialitzar_hash_factures.php

declare(strict_types=1);

global $conn;

/** @var PDO $conn */

echo "Iniciant inicialització de hash_interno...\n";

// 1) Obtener todas las facturas, en orden cronológico
$sql = "
    SELECT
        id,
        serie,
        numero,
        fecha_emision,
        subtotal,
        impuesto_total,
        total,
        facturar_a_nif
    FROM facturas
    ORDER BY fecha_emision ASC, id ASC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo "Factures trobades: " . count($facturas) . "\n";

if (empty($facturas)) {
    echo "No hi ha factures. Res a fer.\n";
    exit;
}

$conn->beginTransaction();

try {
    $hashAnterior = ''; // primera factura no té hash anterior
    $actualitzades = 0;

    $sqlUpdate = "
        UPDATE facturas
        SET hash_interno = :hash_interno,
            hash_interno_anterior = :hash_interno_anterior
        WHERE id = :id
    ";
    $stmtUpdate = $conn->prepare($sqlUpdate);

    foreach ($facturas as $row) {
        $idFactura = (int)$row['id'];

        // Calcula hash per aquesta factura
        $hashActual = calcularHashInternoFacturaFromRow($row, $hashAnterior);

        $stmtUpdate->execute([
            ':hash_interno'          => $hashActual,
            ':hash_interno_anterior' => $hashAnterior !== '' ? $hashAnterior : null,
            ':id'                    => $idFactura,
        ]);

        $hashAnterior = $hashActual;
        $actualitzades++;

        if ($actualitzades % 100 === 0) {
            echo "Actualitzades {$actualitzades} factures...\n";
        }
    }

    $conn->commit();
    echo "Hash inicialitzat per {$actualitzades} factures.\n";
} catch (Throwable $e) {
    $conn->rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
