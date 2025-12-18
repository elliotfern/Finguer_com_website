<?php

function crearFacturaParaReserva(PDO $conn, int $reservaId, string $origen = 'manual'): ?int
{
    $fase = 'inicio';
    $usaTransaccionPropia = !$conn->inTransaction();

    error_log(
        '[FINGUER] crearFacturaParaReserva INICIO '
            . '(reserva_id=' . $reservaId
            . ', origen=' . $origen
            . ', usaTransaccionPropia=' . ($usaTransaccionPropia ? '1' : '0') . ')'
    );

    try {
        // 0) Idempotencia: ¿ya existe factura?
        $fase = 'comprobar_existente';
        $sqlExist = "
            SELECT id, numero
            FROM facturas
            WHERE reserva_id = :reserva_id
            ORDER BY id ASC
            LIMIT 1
        ";
        $stmtExist = $conn->prepare($sqlExist);
        $stmtExist->execute([':reserva_id' => $reservaId]);
        $facturaExistente = $stmtExist->fetch(PDO::FETCH_ASSOC);

        if ($facturaExistente) {
            $idExist = (int)$facturaExistente['id'];
            error_log('[FINGUER] crearFacturaParaReserva EXISTE (reserva_id=' . $reservaId . ', factura_id=' . $idExist . ')');
            return $idExist;
        }

        // 1) Cargar reserva + usuario (snapshot cliente)
        $fase = 'cargar_reserva_usuario';
        $sql = "
            SELECT
                pr.id,
                pr.usuario_id,
                pr.fecha_reserva,
                pr.subtotal_calculado,
                pr.iva_calculado,
                pr.total_calculado,

                u.nombre         AS u_nombre,
                u.email          AS u_email,
                u.empresa        AS u_empresa,
                u.nif            AS u_nif,
                u.direccion      AS u_direccion,
                u.ciudad         AS u_ciudad,
                u.codigo_postal  AS u_cp,
                u.pais           AS u_pais
            FROM parking_reservas pr
            INNER JOIN usuarios u
                ON u.id = pr.usuario_id
            WHERE pr.id = :id
            LIMIT 1
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':id' => $reservaId]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            error_log("[FINGUER] crearFacturaParaReserva: NO SE ENCONTRÓ la reserva id={$reservaId}");
            return null;
        }

        // ✅ fecha_emision = ahora (momento real de emisión) con TZ explícita
        $tz = new DateTimeZone('Europe/Madrid'); // o Europe/Rome
        $fechaEmisionDt = new DateTimeImmutable('now', $tz);
        $fechaEmision = $fechaEmisionDt->format('Y-m-d H:i:s');

        $subtotal  = (float)($reserva['subtotal_calculado'] ?? 0);
        $iva       = (float)($reserva['iva_calculado'] ?? 0);
        $total     = (float)($reserva['total_calculado'] ?? 0);
        $usuarioId = (int)($reserva['usuario_id'] ?? 0);

        // Snapshot cliente (nombre/email siempre, resto opcional)
        $facturarNombre = trim((string)($reserva['u_nombre'] ?? ''));
        $facturarEmail  = trim((string)($reserva['u_email'] ?? ''));

        if ($facturarNombre === '' || $facturarEmail === '') {
            error_log("[FINGUER] crearFacturaParaReserva: faltan datos obligatorios usuario (nombre/email) reserva={$reservaId}");
            return null;
        }

        // En tu tabla facturas: casi todo NOT NULL => fallback '' (empresa sí puede ser NULL)
        $facturarEmpresa   = (isset($reserva['u_empresa']) && $reserva['u_empresa'] !== '') ? (string)$reserva['u_empresa'] : null;
        $facturarNif       = trim((string)($reserva['u_nif'] ?? ''));
        $facturarDireccion = trim((string)($reserva['u_direccion'] ?? ''));
        $facturarCiudad    = trim((string)($reserva['u_ciudad'] ?? ''));
        $facturarCp        = trim((string)($reserva['u_cp'] ?? ''));
        $facturarPais      = trim((string)($reserva['u_pais'] ?? ''));
        if ($facturarPais === '') $facturarPais = 'ES';

        error_log(
            '[FINGUER] crearFacturaParaReserva RESERVA '
                . '(reserva_id=' . $reservaId
                . ', fecha_emision=' . $fechaEmision
                . ', subtotal=' . $subtotal
                . ', iva=' . $iva
                . ', total=' . $total
                . ', usuario_id=' . $usuarioId . ')'
        );

        // 2) Seleccionar emisor (cutover fijo 2026-01-01) y snapshot
        $fase = 'seleccionar_emisor';

        $cutover = new DateTimeImmutable('2026-01-01 00:00:00', $tz);
        $emisorId = ($fechaEmisionDt >= $cutover) ? 2 : 1;

        $sqlEmisor = "
            SELECT id, nombre_legal, nif, direccion, cp, ciudad, pais
            FROM sociedades_emisoras
            WHERE id = :id
            LIMIT 1
        ";
        $stmtE = $conn->prepare($sqlEmisor);
        $stmtE->execute([':id' => $emisorId]);
        $emisor = $stmtE->fetch(PDO::FETCH_ASSOC);

        if (!$emisor) {
            error_log('[FINGUER] crearFacturaParaReserva: NO existe emisor id=' . $emisorId);
            return null;
        }

        $emisorNombre = (string)$emisor['nombre_legal'];
        $emisorNif    = (string)$emisor['nif'];
        $emisorDir    = (string)$emisor['direccion'];
        $emisorCp     = (string)$emisor['cp'];
        $emisorCiudad = (string)$emisor['ciudad'];
        $emisorPais   = (string)$emisor['pais'];

        // 3) Numeración
        $fase = 'generar_numero';
        $serieCodigo = (int)date('Y', strtotime($fechaEmision));
        $numeracion  = generarNumeroFactura($conn, (string)$serieCodigo);

        error_log(
            '[FINGUER] crearFacturaParaReserva NUMERACION '
                . '(reserva_id=' . $reservaId
                . ', serie=' . $numeracion['serie']
                . ', numero=' . $numeracion['numero'] . ')'
        );

        // 4) Transacción
        $fase = 'iniciar_transaccion';
        if ($usaTransaccionPropia) {
            $conn->beginTransaction();
            error_log('[FINGUER] crearFacturaParaReserva BEGIN TRANSACTION (reserva_id=' . $reservaId . ')');
        } else {
            error_log('[FINGUER] crearFacturaParaReserva USA TRANSACCION EXTERNA (reserva_id=' . $reservaId . ')');
        }

        // 5) Insert factura
        $fase = 'insertar_factura';
        $sqlInsF = "
            INSERT INTO facturas
            (
                numero, serie, reserva_id, usuario_id,
                emisor_id, emisor_nombre_legal, emisor_nif, emisor_direccion, emisor_cp, emisor_ciudad, emisor_pais,
                fecha_emision, moneda, subtotal, impuesto_total, total, estado,
                facturar_a_nombre, facturar_a_empresa, facturar_a_nif, facturar_a_direccion, facturar_a_ciudad, facturar_a_cp, facturar_a_pais, facturar_a_email
            ) VALUES (
                :numero, :serie, :reserva_id, :usuario_id,
                :emisor_id, :emisor_nombre_legal, :emisor_nif, :emisor_direccion, :emisor_cp, :emisor_ciudad, :emisor_pais,
                :fecha_emision, 'EUR', :subtotal, :impuesto_total, :total, 'emitida',
                :facturar_a_nombre, :facturar_a_empresa, :facturar_a_nif, :facturar_a_direccion, :facturar_a_ciudad, :facturar_a_cp, :facturar_a_pais, :facturar_a_email
            )
        ";
        $stmtF = $conn->prepare($sqlInsF);

        $okF = $stmtF->execute([
            ':numero' => (string)$numeracion['numero'],
            ':serie'  => (string)$numeracion['serie'],
            ':reserva_id' => $reservaId,
            ':usuario_id' => $usuarioId,

            ':emisor_id' => $emisorId,
            ':emisor_nombre_legal' => $emisorNombre,
            ':emisor_nif' => $emisorNif,
            ':emisor_direccion' => $emisorDir,
            ':emisor_cp' => $emisorCp,
            ':emisor_ciudad' => $emisorCiudad,
            ':emisor_pais' => $emisorPais,

            ':fecha_emision' => $fechaEmision,
            ':subtotal' => $subtotal,
            ':impuesto_total' => $iva,
            ':total' => $total,

            ':facturar_a_nombre' => $facturarNombre,
            ':facturar_a_empresa' => $facturarEmpresa,
            ':facturar_a_nif' => $facturarNif,
            ':facturar_a_direccion' => $facturarDireccion,
            ':facturar_a_ciudad' => $facturarCiudad,
            ':facturar_a_cp' => $facturarCp,
            ':facturar_a_pais' => $facturarPais,
            ':facturar_a_email' => $facturarEmail,
        ]);

        if (!$okF) {
            $info = $stmtF->errorInfo();
            throw new \RuntimeException('Error insertando factura: ' . implode(' | ', $info));
        }

        $facturaId = (int)$conn->lastInsertId();
        error_log('[FINGUER] crearFacturaParaReserva FACTURA_INSERTADA (reserva_id=' . $reservaId . ', factura_id=' . $facturaId . ')');

        // 6) Insertar líneas (COPIA EXACTA desde parking_reservas_servicios)
        $fase = 'cargar_servicios';

        $sqlServ = "
            SELECT
                prs.descripcion,
                prs.cantidad,
                prs.precio_unitario,
                prs.impuesto_percent,
                prs.total_base,
                prs.total_impuesto,
                prs.total_linea
            FROM parking_reservas_servicios prs
            WHERE prs.reserva_id = :reserva_id
            ORDER BY prs.id ASC
        ";
        $stmtServ = $conn->prepare($sqlServ);
        $stmtServ->execute([':reserva_id' => $reservaId]);
        $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

        if (!$servicios) {
            throw new RuntimeException("No hay servicios en parking_reservas_servicios para reserva_id={$reservaId}");
        }

        $fase = 'insertar_lineas';

        $sqlLinea = "
            INSERT INTO facturas_lineas
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
        $sumBase = 0.0;
        $sumIva  = 0.0;
        $sumTot  = 0.0;

        foreach ($servicios as $srv) {
            $base = (float)($srv['total_base'] ?? 0);
            $ivaL = (float)($srv['total_impuesto'] ?? 0);
            $totL = (float)($srv['total_linea'] ?? 0);

            // Guardamos sumas para verificación final
            $sumBase += $base;
            $sumIva  += $ivaL;
            $sumTot  += $totL;

            $ok = $stmtLinea->execute([
                ':factura_id'       => $facturaId,
                ':linea'            => $nLinea++,
                ':descripcion'      => (string)($srv['descripcion'] ?? ''),
                ':cantidad'         => (float)($srv['cantidad'] ?? 0),
                ':precio_unitario'  => (float)($srv['precio_unitario'] ?? 0),
                ':impuesto_percent' => (float)($srv['impuesto_percent'] ?? 0),
                ':total_base'       => $base,
                ':total_impuesto'   => $ivaL,
                ':total_linea'      => $totL,
                ':reserva_id'       => $reservaId,
            ]);

            if (!$ok) {
                $info = $stmtLinea->errorInfo();
                throw new RuntimeException('Error insertando línea de factura: ' . implode(' | ', $info));
            }
        }

        // ✅ Verificación dura: las líneas deben cuadrar con la factura (a 2 decimales)
        $fase = 'verificar_totales_lineas';
        if (
            round($sumBase, 2) !== round($subtotal, 2) ||
            round($sumIva, 2)  !== round($iva, 2) ||
            round($sumTot, 2)  !== round($total, 2)
        ) {
            throw new RuntimeException(
                'Descuadre totales: '
                    . 'sumBase=' . round($sumBase, 2) . ' vs subtotal=' . round($subtotal, 2)
                    . ' | sumIva=' . round($sumIva, 2) . ' vs iva=' . round($iva, 2)
                    . ' | sumTot=' . round($sumTot, 2) . ' vs total=' . round($total, 2)
            );
        }

        // 7) Vincular pago confirmado -> factura_id
        $fase = 'actualizar_pago';
        $sqlPago = "
            UPDATE pagos
            SET factura_id = :factura_id
            WHERE reserva_id = :reserva_id
              AND estado = 'confirmado'
        ";
        $stmtPago = $conn->prepare($sqlPago);
        $stmtPago->execute([':factura_id' => $facturaId, ':reserva_id' => $reservaId]);

        // 8) Hash interno
        $fase = 'hash_interno_select_prev';
        $sqlLast = "
            SELECT hash_interno
            FROM facturas
            WHERE id <> :id AND hash_interno IS NOT NULL
            ORDER BY fecha_emision DESC, id DESC
            LIMIT 1
        ";
        $stmtLast = $conn->prepare($sqlLast);
        $stmtLast->execute([':id' => $facturaId]);
        $lastRow = $stmtLast->fetch(PDO::FETCH_ASSOC);
        $hashAnterior = $lastRow ? (string)$lastRow['hash_interno'] : '';

        $fase = 'hash_interno_select_factura';
        $sqlFactura = "
            SELECT id, serie, numero, fecha_emision, subtotal, impuesto_total, total, facturar_a_nif
            FROM facturas
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
                UPDATE facturas
                SET hash_interno = :hash_interno,
                    hash_interno_anterior = :hash_interno_anterior
                WHERE id = :id
            ";
            $stmtUpdHash = $conn->prepare($sqlUpdHash);
            $okUpd = $stmtUpdHash->execute([
                ':hash_interno' => $hashActual,
                ':hash_interno_anterior' => ($hashAnterior !== '' ? $hashAnterior : null),
                ':id' => $facturaId,
            ]);

            if (!$okUpd) {
                $info = $stmtUpdHash->errorInfo();
                throw new \RuntimeException('Error actualizando hash interno: ' . implode(' | ', $info));
            }
        }

        // 9) Commit
        $fase = 'commit';
        if ($usaTransaccionPropia && $conn->inTransaction()) {
            $conn->commit();
            error_log('[FINGUER] crearFacturaParaReserva COMMIT (reserva_id=' . $reservaId . ', factura_id=' . $facturaId . ')');
        }

        // 10) Log
        $fase = 'log_factura';
        $usuarioBackofficeId = ($origen === 'manual') ? getUsuarioBackofficeIdFromCookie() : null;
        $accionLog = ($origen === 'manual') ? 'creacion' : 'creacion_automatica_redsys';

        registrarLogFactura($conn, $facturaId, $usuarioBackofficeId, $accionLog, [
            'reserva_id' => $reservaId,
            'subtotal' => $subtotal,
            'iva' => $iva,
            'total' => $total,
            'origen' => $origen,
            'emisor_id' => $emisorId,
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
            error_log('[FINGUER] crearFacturaParaReserva ROLLBACK (reserva_id=' . $reservaId . ', fase=' . $fase . ')');
        }

        error_log(
            '[FINGUER] Error en crearFacturaParaReserva '
                . '(reserva_id=' . $reservaId
                . ', fase=' . $fase
                . '): ' . $e->getMessage()
                . ' en ' . $e->getFile() . ':' . $e->getLine()
        );

        // Si ya existe por UNIQUE(reserva_id), devolvemos la existente
        $isDuplicate = false;

        if ($e instanceof \PDOException) {
            $errInfo = $e->errorInfo ?? null;
            $sqlState = is_array($errInfo) ? ($errInfo[0] ?? '') : '';
            if ($sqlState === '23000') {
                $isDuplicate = true;
            }
        }

        if (!$isDuplicate) {
            $msg = $e->getMessage();
            if (stripos($msg, 'Duplicate') !== false || stripos($msg, 'uq_facturas_reserva') !== false) {
                $isDuplicate = true;
            }
        }

        if ($isDuplicate) {
            try {
                $st = $conn->prepare("
                    SELECT id
                    FROM facturas
                    WHERE reserva_id = :rid
                    ORDER BY id ASC
                    LIMIT 1
                ");
                $st->execute([':rid' => $reservaId]);
                $idExist = (int)($st->fetchColumn() ?: 0);
                if ($idExist > 0) {
                    error_log('[FINGUER] crearFacturaParaReserva DUPLICATE->RETURN_EXISTING (reserva_id=' . $reservaId . ', factura_id=' . $idExist . ')');
                    return $idExist;
                }
            } catch (\Throwable $e2) {
                // si esto falla, seguimos con el flujo normal de error
            }
        }

        return null;
    }
}
