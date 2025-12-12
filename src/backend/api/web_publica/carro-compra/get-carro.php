<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit();
}

$session = trim((string)($_GET['session'] ?? ''));
if ($session === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Missing session']);
    exit();
}

// Conexión PDO como usas en otros endpoints
global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'DB connection not available']);
    exit();
}

try {
    $stmt = $conn->prepare("
        SELECT
            session,
            subtotal_sin_iva,
            iva_total,
            total_con_iva,
            lineas_json,
            hash,
            updated_at
        FROM carro_compra
        WHERE session = :session
        LIMIT 1
    ");
    $stmt->execute([':session' => $session]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'error' => 'Carrito no encontrado']);
        exit();
    }

    $snapshot = null;
    if (!empty($row['lineas_json'])) {
        $decoded = json_decode((string)$row['lineas_json'], true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $snapshot = $decoded;
        }
    }

    echo json_encode([
        'ok' => true,
        'session' => (string)$row['session'],
        'subtotal' => (float)$row['subtotal_sin_iva'],
        'iva_total' => (float)$row['iva_total'],
        'total' => (float)$row['total_con_iva'],
        'snapshot' => $snapshot,   // <- aquí va seleccion + lineas + totales (si lo guardaste así)
        'hash' => $row['hash'],
        'updated_at' => $row['updated_at'],
    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => 'Server error']);
}
