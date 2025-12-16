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
if ($type !== 'emitir-factura') {
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', ['allowed' => ['emitir-factura']]), 400);
}

$input = readJsonBody(true);
$reservaId = isset($input['reserva_id']) ? (int)$input['reserva_id'] : 0;

if ($reservaId <= 0) {
    jsonResponse(vp2_err('Parámetro reserva_id inválido', 'BAD_ID'), 400);
}

try {
    // 1) Intentar crear la factura para la reserva (si no existe)
    $facturaId = crearFacturaParaReserva($conn, $reservaId, 'manual');

    if ($facturaId === null) {
        jsonResponse(vp2_err('Error al crear la factura para la reserva', 'ERROR_CREACION_FACTURA'), 500);
    }

    // 2) Generar el PDF
    $pdfRes = generarFacturaPdf($facturaId, [
        'mode'     => 'F', // Guardar en archivo
        'base_dir' => '/home/epgylzqu/finguer.com', // Ruta completa a la raíz de tu servidor
        'subdir'   => '/pdf/facturas', // Subdirectorio donde se guardará el PDF
        'force'    => false, // No forzar la generación si ya existe
    ]);

    if ($pdfRes['status'] !== 'success') {
        jsonResponse(vp2_err('Error al generar el PDF', 'ERROR_GENERACION_PDF', $pdfRes), 500);
    }
    // 3) Enviar la ruta pública del PDF en la respuesta
    $pdfUrl = 'https://finguer.com/pdf/facturas/' . basename($pdfRes['path']); // Genera la URL pública

    jsonResponse(vp2_ok('Factura generada correctamente', [
        'factura_id'    => $facturaId,
        'pdf_url'       => $pdfUrl, // URL pública del PDF generado
    ]), 200);

    // Si todo ha ido bien, devolver la respuesta
    jsonResponse(vp2_ok('Factura generada y enviada correctamente', [
        'factura_id'    => $facturaId,
        'pdf_url'       => $pdfUrl,
        'email_sent'    => $emailRes['status'] === 'success',
    ]), 200);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error al procesar la solicitud de factura', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
