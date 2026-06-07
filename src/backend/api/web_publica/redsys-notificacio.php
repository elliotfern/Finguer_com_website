<?php

declare(strict_types=1);
ini_set('error_log', __DIR__ . '/redsys_debug.log');

use App\Payments\RedsysSignatureVerifier;

header('Content-Type: text/plain');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('METHOD NOT ALLOWED');
}

$version   = $_POST['Ds_SignatureVersion'] ?? null;
$params    = $_POST['Ds_MerchantParameters'] ?? null;
$signature = $_POST['Ds_Signature'] ?? null;

if (!$params || !$signature) {
    http_response_code(400);
    exit('MISSING DATA');
}

$decoded = json_decode(base64_decode($params), true);

// Justo después de $decoded = json_decode(...)
error_log('REDSYS NOTIFICATION: ' . json_encode([
    'order'    => $decoded['Ds_Order'] ?? 'NULL',
    'response' => $decoded['Ds_Response'] ?? 'NULL',
    'amount'   => $decoded['Ds_Amount'] ?? 'NULL',
]));

if (!$decoded) {
    http_response_code(400);
    exit('INVALID PARAMETERS');
}

$order    = $decoded['Ds_Order'] ?? null;
$response = $decoded['Ds_Response'] ?? null;

if (!$order) {
    http_response_code(400);
    exit('NO ORDER');
}

// 🔐 validación firma
$verifier = new RedsysSignatureVerifier($_ENV['REDSYS_SECRET_KEY']);

if (!$verifier->check($params, $signature, $order)) {
    http_response_code(403);
    exit('INVALID SIGNATURE');
}

global $conn; /** @var PDO $conn */

// buscar reserva
$stmt = $conn->prepare("
    SELECT id, estado
    FROM parking_reservas
    WHERE localizador = :order
    LIMIT 1
");

$stmt->execute(['order' => $order]);
$reserva = $stmt->fetch(PDO::FETCH_ASSOC);

error_log('RESERVA FOUND: ' . ($reserva ? json_encode($reserva) : 'NOT FOUND'));
error_log('=== REDSYS NOTIF === order=[' . ($decoded['Ds_Order'] ?? 'NULL') . '] response=[' . ($decoded['Ds_Response'] ?? 'NULL') . ']');

if (!$reserva) {
    http_response_code(404);
    exit('ORDER NOT FOUND');
}

// pago OK en Redsys
$isPaid = ($response === '0000');

// idempotencia
if ($reserva['estado'] === 'pagada' && $isPaid) {
    http_response_code(200);
    exit('ALREADY PROCESSED');
}

if ($isPaid) {

    $conn->prepare("
        UPDATE parking_reservas
        SET estado = 'pagada',
            updated_at = NOW()
        WHERE id = :id
    ")->execute([
        'id' => $reserva['id']
    ]);

    try {
        //registrarCobroConfirmado((int)$reserva['id']);
    } catch (\Throwable $e) {
        error_log('EMAIL/FACTURA ERROR: ' . $e->getMessage());
    }

} else {

    // opcional: marcar fallo si quieres trazabilidad
    $conn->prepare("
        UPDATE parking_reservas
        SET estado = 'cancelada'
        WHERE id = :id
    ")->execute([
        'id' => $reserva['id']
    ]);
}

http_response_code(200);
echo "OK";
exit;