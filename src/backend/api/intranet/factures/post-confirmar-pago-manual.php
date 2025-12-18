<?php

declare(strict_types=1);

requireMethod('POST');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

$type = (string)($_GET['type'] ?? '');

try {

    // =========================================================
    // type=pagoManual  (registrar pago manual + crear factura)
    // =========================================================
    if ($type === 'pagoManual') {

        // Body JSON
        $input = readJsonBody(); // <-- asumo que lo tienes; si no, te lo pongo abajo
        $reservaId = (int)($input['reserva_id'] ?? 0);

        if ($reservaId <= 0) {
            jsonResponse(vp2_err('Falta reserva_id', 'MISSING_RESERVA_ID'), 400);
        }

        // Opcionales: por si luego quieres activar envío desde intranet
        $crearFactura  = (bool)($input['crear_factura'] ?? true);
        $enviarFactura = (bool)($input['enviar_factura'] ?? false);

        // 1) Leer reserva
        $p1 = lecturaReserva($conn, $reservaId);
        if (($p1['status'] ?? '') !== 'success') {
            jsonResponse(array_merge($p1, ['step' => 1]), 400);
        }

        $reserva = $p1['data']['reserva'];
        $importe = (float)($reserva['total_calculado'] ?? 0);
        $ref     = (string)($reserva['localizador'] ?? '');

        if ($importe <= 0) {
            jsonResponse(vp2_err('La reserva no tiene total_calculado válido', 'BAD_AMOUNT', [
                'reserva_id' => $reservaId,
                'total_calculado' => $reserva['total_calculado'] ?? null,
            ]), 409);
        }

        // 2) Idempotencia (evitar duplicados)
        //    - si ya hay un pago confirmado, no lo repetimos
        //    - si ya hay factura, no la duplicamos
        $stmtPago = $conn->prepare("
            SELECT id, factura_id, fecha, metodo, estado, pasarela, importe
            FROM pagos
            WHERE reserva_id = :rid AND estado = 'confirmado'
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtPago->execute([':rid' => $reservaId]);
        $pagoExistente = $stmtPago->fetch(PDO::FETCH_ASSOC) ?: null;

        $facturaExistenteId = obtenerFacturaIdPorReserva($conn, $reservaId);

        // Si ya estaba TODO hecho, devolvemos OK sin efectos secundarios
        if ($pagoExistente && $facturaExistenteId) {
            jsonResponse(vp2_ok('OK (ya procesado)', [
                'reserva' => $reserva,
                'pago'    => $pagoExistente,
                'factura' => ['status' => 'existing', 'id' => (int)$facturaExistenteId],
            ], ['step' => 0, 'idempotent' => true]));
        }

        // 3) Transacción: registrar pago + (opcional) factura
        $conn->beginTransaction();

        // 3A) Si no existe pago confirmado -> lo creamos como MANUAL (TPV)
        $pagoData = $pagoExistente;

        if (!$pagoExistente) {
            $p3 = registrarCobroConfirmado($conn, (int)$reserva['id'], [
                'metodo'     => 'tarjeta',
                'pasarela'   => 'TPV',        // ✅ lo que pedías
                'referencia' => $ref ?: ('MANUAL-' . $reservaId),
                'importe'    => $importe,     // ✅ usa el total_calculado
                // si tu registrarCobroConfirmado soporta "raw_respuesta" u otros, puedes meter nota:
                // 'raw_respuesta' => 'Pago manual intranet',
            ]);

            if (($p3['status'] ?? '') !== 'success') {
                $conn->rollBack();
                jsonResponse(array_merge($p3, ['step' => 3]), 400);
            }

            $pagoData = $p3['data']['pago'] ?? null;
        }

        // 3B) Crear factura (si procede)
        $facturaId = $facturaExistenteId ? (int)$facturaExistenteId : null;

        if ($crearFactura && !$facturaId) {
            // El string de origen aquí lo usas tú (en tu código usas 'redsys')
            // Yo pondría 'manual' o 'tpv' para trazabilidad.
            $facturaId = crearFacturaParaReserva($conn, (int)$reserva['id'], 'manual');

            if (!$facturaId) {
                $conn->rollBack();
                jsonResponse(vp2_err(
                    'Pago registrado, pero falló la creación de factura',
                    'FACTURA_CREATE_FAILED',
                    ['step' => 4, 'reserva_id' => $reservaId]
                ), 500);
            }
        }

        // 3C) Enlazar pago.factura_id (tu tabla pagos lo tiene)
        //     - solo si tenemos pagoId y facturaId y aún no está enlazado
        $pagoId = (int)($pagoData['id'] ?? 0);
        if ($pagoId > 0 && $facturaId) {
            $stmtLink = $conn->prepare("
                UPDATE pagos
                SET factura_id = :fid
                WHERE id = :pid AND (factura_id IS NULL OR factura_id = 0)
            ");
            $stmtLink->execute([
                ':fid' => (int)$facturaId,
                ':pid' => $pagoId,
            ]);
        }

        // 3D) (Opcional) actualizar estado reserva a "pago_oficina" o el que uses
        //     Tú tienes enum estado con 'pago_oficina...' (truncado en tu captura).
        //     Ajusta exactamente el literal correcto.
        $stmtUpd = $conn->prepare("
            UPDATE parking_reservas
            SET estado = 'pago_oficina'
            WHERE id = :rid
        ");
        $stmtUpd->execute([':rid' => $reservaId]);

        $conn->commit();

        // 4) (Opcional fuera TX) enviar factura por email
        $envio = null;
        if ($enviarFactura && $facturaId) {
            $envio = enviarFacturaPorEmail($conn, (int)$facturaId, [
                'origen' => 'intranet',
                'force_send' => true,
                'skip_if_already_sent' => false,
            ]);
        }

        jsonResponse(vp2_ok('OK', [
            'reserva' => $reserva,
            'pago'    => $pagoData,
            'factura' => $facturaId ? ['status' => 'success', 'id' => (int)$facturaId] : null,
            'envio_factura' => $envio,
        ], ['step' => 6]));
    }

    // Si llega aquí, type no válido
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', [
        'allowed' => ['pagoManual']
    ]), 400);
} catch (Throwable $e) {
    if ($conn instanceof PDO && $conn->inTransaction()) {
        $conn->rollBack();
    }
    jsonResponse(vp2_err('Error interno', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
