<?php

function verificarPagament(int $reservaId, array $opts = []): array
{
    $opts = array_merge([
        'solo_info'           => true,
        'actualizar_bd'       => false,
        'enviar_confirmacion' => false,
        'crear_factura'       => false,
        'enviar_factura'      => false,

        // extra útil:
        'origen'              => 'cron', // 'cron'|'intranet'
        'force_confirmacion'  => false,  // intranet: true si quieres reenvío
        'force_factura_email' => false,  // intranet: true para reenviar factura
    ], $opts);

    $reservaId = (int)$reservaId;
    if ($reservaId <= 0) {
        return vp2_err('No se ha seleccionado ninguna reserva.', 'RESERVA_ID_MISSING');
    }

    global $conn;
    /** @var PDO $conn */

    // STEP 1
    $p1 = lecturaReserva($conn, $reservaId);
    if (($p1['status'] ?? '') !== 'success') return array_merge($p1, ['step' => 1]);

    $reserva = $p1['data']['reserva'];
    $order   = (string)$reserva['localizador'];

    // STEP 2
    $p2 = consultaRedsys($order);
    if (($p2['status'] ?? '') !== 'success') {
        return array_merge($p2, [
            'step' => 2,
            'data' => array_merge($p2['data'] ?? [], ['reserva' => $reserva, 'opts' => $opts]),
        ]);
    }

    $redsys = $p2['data']['redsys'] ?? [];
    $paid   = (bool)($redsys['paid'] ?? false);

    if (!$paid) {
        return vp2_ok($p2['message'] ?? 'Redsys: pago no confirmado.', [
            'reserva' => $reserva,
            'redsys' => $redsys,
            'opts' => $opts,
        ], ['step' => 2]);
    }

    if (empty($opts['actualizar_bd'])) {
        return vp2_ok($p2['message'] ?? 'Redsys: pago confirmado.', [
            'reserva' => $reserva,
            'redsys' => $redsys,
            'opts' => $opts,
        ], ['step' => 2]);
    }

    // STEP 3
    $p3 = registrarCobroConfirmado($conn, (int)$reserva['id'], [
        'metodo'     => 'tarjeta',
        'pasarela'   => 'REDSYS',
        'referencia' => (string)$reserva['localizador'],
        'importe'    => null,
    ]);
    if (($p3['status'] ?? '') !== 'success') {
        return array_merge($p3, [
            'step' => 3,
            'data' => array_merge($p3['data'] ?? [], ['reserva' => $reserva, 'redsys' => $redsys, 'opts' => $opts]),
        ]);
    }

    $data = [
        'reserva' => $reserva,
        'redsys'  => $redsys,
        'pago'    => $p3['data']['pago'] ?? null,
        'opts'    => $opts,
    ];

    // STEP 4: crear factura (opcional)
    $facturaId = null;

    if (!empty($opts['crear_factura'])) {
        $facturaId = crearFacturaParaReserva($conn, (int)$reserva['id'], 'redsys');
        if (!$facturaId) {
            return vp2_ok(
                'Pago confirmado y BD actualizada, pero falló la creación de factura.',
                array_merge($data, ['factura' => ['status' => 'error', 'id' => null]]),
                ['step' => 4, 'warning' => true]
            );
        }
        $data['factura'] = ['status' => 'success', 'id' => (int)$facturaId];
    } else {
        // si no creamos, intentamos localizar una existente (para enviar_factura)
        $facturaId = obtenerFacturaIdPorReserva($conn, (int)$reserva['id']);
        if ($facturaId) {
            $data['factura'] = ['status' => 'existing', 'id' => (int)$facturaId];
        }
    }

    // STEP 5: enviar confirmación (opcional)
    if (!empty($opts['enviar_confirmacion'])) {
        $p5 = enviarConfirmacionReserva($conn, (int)$reserva['id'], [
            'force' => (bool)$opts['force_confirmacion'], // cron false, intranet true si quieres
        ]);
        $data['confirmacion'] = $p5;

        if (($p5['status'] ?? '') !== 'success') {
            return vp2_ok(
                'Pago confirmado y BD actualizada, pero falló el envío de confirmación.',
                $data,
                ['step' => 5, 'warning' => true]
            );
        }
    }

    // STEP 6: enviar factura (opcional)
    if (!empty($opts['enviar_factura'])) {
        if (!$facturaId) {
            return vp2_ok(
                'Pago confirmado y BD actualizada, pero no existe factura para enviar.',
                $data,
                ['step' => 6, 'warning' => true]
            );
        }

        $p6 = enviarFacturaPorEmail($conn, (int)$facturaId, [
            'origen'        => $opts['origen'] ?? 'cron',
            'force_send'    => (bool)$opts['force_factura_email'], // intranet true para reenvío
            'force_pdf'     => false,
            'skip_if_already_sent' => ($opts['origen'] ?? 'cron') === 'cron',
        ]);

        $data['envio_factura'] = $p6;

        if (($p6['status'] ?? '') !== 'success') {
            return vp2_ok(
                'Pago confirmado y BD actualizada, pero falló el envío de la factura.',
                $data,
                ['step' => 6, 'warning' => true]
            );
        }
    }

    return vp2_ok(
        'Pago confirmado y flujo completado.',
        $data,
        ['step' => !empty($opts['enviar_factura']) ? 6 : (!empty($opts['enviar_confirmacion']) ? 5 : 4)]
    );
}
