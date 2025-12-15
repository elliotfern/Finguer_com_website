<?php

function crearFacturaParaReserva(PDO $conn, int $reservaId, string $origen = 'manual'): ?int
{
    $fase = 'inicio';

    // ¿Hay ya una transacción activa?
    $usaTransaccionPropia = !$conn->inTransaction();

    // LOG: entrada a la función
    error_log(
        '[FINGUER] crearFacturaParaReserva INICIO '
            . '(reserva_id=' . $reservaId
            . ', origen=' . $origen
            . ', usaTransaccionPropia=' . ($usaTransaccionPropia ? '1' : '0') . ')'
    );

    try {
        // 0) Comprobar si ya existe una factura para esta reserva
        $fase = 'comprobar_existente';

        $sqlExist = "
            SELECT id, numero
            FROM epgylzqu_parking_finguer_v2.facturas
            WHERE reserva_id = :reserva_id
            LIMIT 1
        ";
        $stmtExist = $conn->prepare($sqlExist);
        $stmtExist->bindParam(':reserva_id', $reservaId, PDO::PARAM_INT);
        $stmtExist->execute();
        $facturaExistente = $stmtExist->fetch(PDO::FETCH_ASSOC);

        if ($facturaExistente) {
            $idExist = (int)$facturaExistente['id'];
            error_log(
                '[FINGUER] crearFacturaParaReserva EXISTE '
                    . '(reserva_id=' . $reservaId
                    . ', factura_id=' . $idExist . ')'
            );
            return $idExist;
        }

        // 1) Coger reserva
        $fase = 'cargar_reserva';

        $sql = "
            SELECT
                pr.id,
                pr.usuario_id,
                pr.fecha_reserva,
                pr.subtotal_calculado,
                pr.iva_calculado,
                pr.total_calculado
            FROM epgylzqu_parking_finguer_v2.parking_reservas pr
            WHERE pr.id = :id
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $reservaId, PDO::PARAM_INT);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            error_log("[FINGUER] crearFacturaParaReserva: NO SE ENCONTRÓ la reserva id={$reservaId}");
            return null;
        }

        $fechaEmision = $reserva['fecha_reserva'] ?? date('Y-m-d H:i:s');

        $subtotal  = (float)$reserva['subtotal_calculado'];
        $iva       = (float)$reserva['iva_calculado'];
        $total     = (float)$reserva['total_calculado'];
        $usuarioId = (int)$reserva['usuario_id'];

        error_log(
            '[FINGUER] crearFacturaParaReserva RESERVA '
                . '(reserva_id=' . $reservaId
                . ', fecha_emision=' . $fechaEmision
                . ', subtotal=' . $subtotal
                . ', iva=' . $iva
                . ', total=' . $total
                . ', usuario_id=' . $usuarioId . ')'
        );

        // 2) Calcular el siguiente número de factura
        $fase = 'generar_numero';

        $serieCodigo = (int)date('Y', strtotime($fechaEmision)); // 2025, 2026...
        $numeracion  = generarNumeroFactura($conn, (string)$serieCodigo);

        error_log(
            '[FINGUER] crearFacturaParaReserva NUMERACION '
                . '(reserva_id=' . $reservaId
                . ', serie=' . $numeracion['serie']
                . ', numero=' . $numeracion['numero'] . ')'
        );

        // 3) Empezar transacción (si es propia)
        $fase = 'iniciar_transaccion';

        if ($usaTransaccionPropia) {
            $conn->beginTransaction();
            error_log(
                '[FINGUER] crearFacturaParaReserva BEGIN TRANSACTION '
                    . '(reserva_id=' . $reservaId . ')'
            );
        } else {
            error_log(
                '[FINGUER] crearFacturaParaReserva USA TRANSACCION EXTERNA '
                    . '(reserva_id=' . $reservaId . ')'
            );
        }

        // 4) Insertar en facturas
        $fase = 'insertar_factura';

        $sqlInsF = "
            INSERT INTO epgylzqu_parking_finguer_v2.facturas
            (
                numero,
                serie,
                reserva_id,
                usuario_id,
                fecha_emision,
                subtotal,
                impuesto_total,
                total,
                estado
            ) VALUES (
                :numero,
                :serie,
                :reserva_id,
                :usuario_id,
                :fecha_emision,
                :subtotal,
                :impuesto_total,
                :total,
                'emitida'
            )
        ";
        $stmtF = $conn->prepare($sqlInsF);
        $stmtF->bindParam(':numero',        $numeracion['numero'],  PDO::PARAM_STR);
        $stmtF->bindParam(':serie',         $numeracion['serie'],   PDO::PARAM_STR);
        $stmtF->bindParam(':reserva_id',    $reservaId,             PDO::PARAM_INT);
        $stmtF->bindParam(':usuario_id',    $usuarioId,             PDO::PARAM_INT);
        $stmtF->bindParam(':fecha_emision', $fechaEmision,          PDO::PARAM_STR);
        $stmtF->bindParam(':subtotal',      $subtotal);
        $stmtF->bindParam(':impuesto_total', $iva);
        $stmtF->bindParam(':total',         $total);

        if (!$stmtF->execute()) {
            $info = $stmtF->errorInfo();
            throw new \RuntimeException('Error insertando factura: ' . implode(' | ', $info));
        }

        $facturaId = (int)$conn->lastInsertId();

        error_log(
            '[FINGUER] crearFacturaParaReserva FACTURA_INSERTADA '
                . '(reserva_id=' . $reservaId
                . ', factura_id=' . $facturaId . ')'
        );

        // 5) Insertar líneas
        $fase = 'cargar_servicios';

        $sqlServ = "
            SELECT
                prs.servicio_id,
                prs.descripcion,
                prs.cantidad,
                prs.precio_unitario,
                prs.impuesto_percent,
                prs.total_base
            FROM epgylzqu_parking_finguer_v2.parking_reservas_servicios prs
            WHERE prs.reserva_id = :reserva_id
            ORDER BY prs.id ASC
        ";
        $stmtServ = $conn->prepare($sqlServ);
        $stmtServ->bindParam(':reserva_id', $reservaId, PDO::PARAM_INT);
        $stmtServ->execute();
        $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

        error_log(
            '[FINGUER] crearFacturaParaReserva SERVICIOS '
                . '(reserva_id=' . $reservaId
                . ', num_servicios=' . count($servicios) . ')'
        );

        $fase = 'insertar_lineas';

        $sqlLinea = "
            INSERT INTO epgylzqu_parking_finguer_v2.facturas_lineas
            (
                factura_id,
                linea,
                descripcion,
                cantidad,
                precio_unitario,
                impuesto_percent,
                total_base,
                total_impuesto,
                total_linea,
                reserva_id
            ) VALUES (
                :factura_id,
                :linea,
                :descripcion,
                :cantidad,
                :precio_unitario,
                :impuesto_percent,
                :total_base,
                :total_impuesto,
                :total_linea,
                :reserva_id
            )
        ";
        $stmtLinea = $conn->prepare($sqlLinea);

        $nLinea = 1;
        foreach ($servicios as $srv) {
            $base   = (float)$srv['total_base'];
            $ivaPrc = (float)$srv['impuesto_percent'];

            $totalImp   = round($base * $ivaPrc / 100, 2);
            $totalLinea = $base + $totalImp;

            $ok = $stmtLinea->execute([
                ':factura_id'      => $facturaId,
                ':linea'           => $nLinea++,
                ':descripcion'     => $srv['descripcion'],
                ':cantidad'        => $srv['cantidad'],
                ':precio_unitario' => $srv['precio_unitario'],
                ':impuesto_percent' => $ivaPrc,
                ':total_base'      => $base,
                ':total_impuesto'  => $totalImp,
                ':total_linea'     => $totalLinea,
                ':reserva_id'      => $reservaId,
            ]);

            if (!$ok) {
                $info = $stmtLinea->errorInfo();
                throw new \RuntimeException('Error insertando línea de factura: ' . implode(' | ', $info));
            }
        }

        // 6) Vincular pago
        $fase = 'actualizar_pago';

        $sqlPago = "
            UPDATE epgylzqu_parking_finguer_v2.pagos
            SET factura_id = :factura_id
            WHERE reserva_id = :reserva_id
              AND estado = 'confirmado'
        ";
        $stmtPago = $conn->prepare($sqlPago);
        $stmtPago->execute([
            ':factura_id' => $facturaId,
            ':reserva_id' => $reservaId,
        ]);

        // 7) Hash interno
        $fase = 'hash_interno_select_prev';

        $sqlLast = "
            SELECT hash_interno
            FROM epgylzqu_parking_finguer_v2.facturas
            WHERE id <> :id
              AND hash_interno IS NOT NULL
            ORDER BY fecha_emision DESC, id DESC
            LIMIT 1
        ";
        $stmtLast = $conn->prepare($sqlLast);
        $stmtLast->execute([':id' => $facturaId]);
        $lastRow = $stmtLast->fetch(PDO::FETCH_ASSOC);

        $hashAnterior = $lastRow ? (string)$lastRow['hash_interno'] : '';

        $fase = 'hash_interno_select_factura';

        $sqlFactura = "
            SELECT
                id,
                serie,
                numero,
                fecha_emision,
                subtotal,
                impuesto_total,
                total,
                facturar_a_nif
            FROM epgylzqu_parking_finguer_v2.facturas
            WHERE id = :id
            LIMIT 1
        ";
        $stmtFac = $conn->prepare($sqlFactura);
        $stmtFac->execute([':id' => $facturaId]);
        $rowFactura = $stmtFac->fetch(PDO::FETCH_ASSOC);

        if ($rowFactura) {
            $fase = 'hash_interno_calcular';

            $hashActual = calcularHashInternoFacturaFromRow($rowFactura, $hashAnterior);

            $fase = 'hash_interno_update';

            $sqlUpdHash = "
                UPDATE epgylzqu_parking_finguer_v2.facturas
                SET hash_interno = :hash_interno,
                    hash_interno_anterior = :hash_interno_anterior
                WHERE id = :id
            ";
            $stmtUpdHash = $conn->prepare($sqlUpdHash);
            $okUpd = $stmtUpdHash->execute([
                ':hash_interno'          => $hashActual,
                ':hash_interno_anterior' => $hashAnterior !== '' ? $hashAnterior : null,
                ':id'                    => $facturaId,
            ]);

            if (!$okUpd) {
                $info = $stmtUpdHash->errorInfo();
                throw new \RuntimeException('Error actualizando hash interno: ' . implode(' | ', $info));
            }
        }

        // 8) Commit
        $fase = 'commit';

        if ($usaTransaccionPropia && $conn->inTransaction()) {
            $conn->commit();
            error_log(
                '[FINGUER] crearFacturaParaReserva COMMIT '
                    . '(reserva_id=' . $reservaId
                    . ', factura_id=' . $facturaId . ')'
            );
        } else {
            error_log(
                '[FINGUER] crearFacturaParaReserva SIN_COMMIT_PROPIO '
                    . '(reserva_id=' . $reservaId
                    . ', factura_id=' . $facturaId . ')'
            );
        }

        // 9) Log de factura
        $fase = 'log_factura';

        $usuarioBackofficeId = ($origen === 'manual')
            ? getUsuarioBackofficeIdFromCookie()
            : null;

        $accionLog = ($origen === 'manual')
            ? 'creacion'
            : 'creacion_automatica_redsys';

        registrarLogFactura($conn, $facturaId, $usuarioBackofficeId, $accionLog, [
            'reserva_id' => $reservaId,
            'subtotal'   => $subtotal,
            'iva'        => $iva,
            'total'      => $total,
            'origen'     => $origen,
        ]);

        error_log(
            '[FINGUER] crearFacturaParaReserva OK '
                . '(reserva_id=' . $reservaId
                . ', factura_id=' . $facturaId
                . ', serie=' . $numeracion['serie']
                . ', numero=' . $numeracion['numero'] . ')'
        );

        return $facturaId;
    } catch (\Throwable $e) {
        if ($usaTransaccionPropia && $conn->inTransaction()) {
            $conn->rollBack();
            error_log(
                '[FINGUER] crearFacturaParaReserva ROLLBACK '
                    . '(reserva_id=' . $reservaId
                    . ', fase=' . $fase . ')'
            );
        }

        error_log(
            '[FINGUER] Error en crearFacturaParaReserva '
                . '(reserva_id=' . $reservaId .
                ', fase=' . $fase .
                '): ' . $e->getMessage() .
                ' en ' . $e->getFile() . ':' . $e->getLine()
        );

        return null;
    }
}
