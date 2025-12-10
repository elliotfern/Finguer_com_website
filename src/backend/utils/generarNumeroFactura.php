<?php

/**
 * Genera el siguiente número de factura para una serie dada.
 *
 * En tu modelo:
 *   - facturas.serie  = '2025'
 *   - facturas.numero = '00001'
 *
 * Devuelve:
 * [
 *   'serie'  => '2025',
 *   'numero' => '00001'
 * ]
 */

function generarNumeroFactura(PDO $conn, string $codigoSerie, int $padding = 5): array
{
    // Empezamos transacción para evitar colisiones en concurrencia
    $conn->beginTransaction();

    // 1) Leer (y bloquear) la fila de la serie
    $sql = "SELECT id, siguiente_numero 
            FROM facturas_series 
            WHERE codigo_serie = :serie
            LIMIT 1
            FOR UPDATE";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':serie', $codigoSerie, PDO::PARAM_STR);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        // Opcional: crear la serie si no existe
        $sqlInsert = "INSERT INTO facturas_series (codigo_serie, descripcion, siguiente_numero)
                      VALUES (:serie, '', 1)";
        $stmtInsert = $conn->prepare($sqlInsert);
        $stmtInsert->bindParam(':serie', $codigoSerie, PDO::PARAM_STR);
        $stmtInsert->execute();

        $serieId         = (int)$conn->lastInsertId();
        $siguienteNumero = 1;
    } else {
        $serieId         = (int)$row['id'];
        $siguienteNumero = (int)$row['siguiente_numero'];
    }

    // 2) Construir solo la parte numérica con padding: '00001', '00002', etc.
    $numeroSecuencia = str_pad((string)$siguienteNumero, $padding, '0', STR_PAD_LEFT);

    // 3) Incrementar para la próxima factura
    $sqlUpdate = "UPDATE facturas_series
                  SET siguiente_numero = :nuevo
                  WHERE id = :id";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $nuevo = $siguienteNumero + 1;
    $stmtUpdate->bindParam(':nuevo', $nuevo, PDO::PARAM_INT);
    $stmtUpdate->bindParam(':id', $serieId, PDO::PARAM_INT);
    $stmtUpdate->execute();

    $conn->commit();

    return [
        'serie'  => $codigoSerie,   // '2025'
        'numero' => $numeroSecuencia, // '00001'
    ];
}
