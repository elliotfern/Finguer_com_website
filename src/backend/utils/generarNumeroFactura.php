<?php

/**
 * Genera el siguiente número de factura para una serie dada.
 *
 * Usa la tabla facturas_series:
 *  - codigo_serie       (p.ej. '2025')
 *  - siguiente_numero   (INT)
 *
 * Devuelve:
 * [
 *   'serie'  => '2025',
 *   'numero' => '00001'
 * ]
 */
function generarNumeroFactura(PDO $conn, string $codigoSerie, int $padding = 5): array
{
    // ¿Hay ya una transacción activa?
    // Si NO la hay, esta función abrirá/gestionará su propia transacción.
    // Si SÍ la hay, usará la existente y NO hará begin/commit/rollback.
    $usaTransaccionPropia = !$conn->inTransaction();

    try {
        if ($usaTransaccionPropia) {
            $conn->beginTransaction();
        }

        // 1) Leer (y bloquear) la fila de la serie
        $sql = "
            SELECT id, siguiente_numero 
            FROM epgylzqu_parking_finguer_v2.facturas_series 
            WHERE codigo_serie = :serie
            LIMIT 1
            FOR UPDATE
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':serie', $codigoSerie, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            // Si no existe la serie, la creamos empezando en 1
            $sqlInsert = "
                INSERT INTO epgylzqu_parking_finguer_v2.facturas_series
                    (codigo_serie, siguiente_numero)
                VALUES
                    (:serie, 1)
            ";
            $stmtInsert = $conn->prepare($sqlInsert);
            $stmtInsert->bindParam(':serie', $codigoSerie, PDO::PARAM_STR);
            $stmtInsert->execute();

            $serieId         = (int)$conn->lastInsertId();
            $siguienteNumero = 1;
        } else {
            $serieId         = (int)$row['id'];
            $siguienteNumero = (int)$row['siguiente_numero'];
        }

        // 2) Construir la parte numérica con padding: '00001', '00002', etc.
        $numeroSecuencia = str_pad((string)$siguienteNumero, $padding, '0', STR_PAD_LEFT);

        // 3) Incrementar para la próxima factura
        $sqlUpdate = "
            UPDATE epgylzqu_parking_finguer_v2.facturas_series
            SET siguiente_numero = :nuevo
            WHERE id = :id
        ";
        $stmtUpdate = $conn->prepare($sqlUpdate);
        $nuevo = $siguienteNumero + 1;
        $stmtUpdate->bindParam(':nuevo', $nuevo, PDO::PARAM_INT);
        $stmtUpdate->bindParam(':id', $serieId, PDO::PARAM_INT);
        $stmtUpdate->execute();

        if ($usaTransaccionPropia) {
            $conn->commit();
        }

        return [
            'serie'  => $codigoSerie,      // '2025'
            'numero' => $numeroSecuencia,  // '00001'
        ];
    } catch (\Throwable $e) {
        if ($usaTransaccionPropia && $conn->inTransaction()) {
            $conn->rollBack();
        }

        // Muy importante: relanzamos la excepción,
        // para que crearFacturaParaReserva() la capture y la loguee.
        throw $e;
    }
}
