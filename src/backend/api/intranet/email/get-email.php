<?php

// Verificar si el método de la solicitud es GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// Verificar si el token está presente en las cookies
if (!isset($_COOKIE['token'])) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Access not allowed']);
    exit();
}

$token = $_COOKIE['token'];

// Verificar el token aquí según tus requerimientos
if (!function_exists('validarToken') || !validarToken($token)) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Invalid token']);
    exit();
}

// 1) Enviar confirmació email reserva
// GET https://finguer.com/control/api/intranet/email/?type=emailConfirmacioReserva&id=11
if (isset($_GET['type']) && $_GET['type'] == 'emailConfirmacioReserva' && isset($_GET['id']) && is_numeric($_GET['id'])) {
    $reservaId = (int)$_GET['id'];

    try {
        $result = enviarConfirmacionReserva($conn, $reservaId, [
            'force' => true, // 👈 intranet SIEMPRE puede reenviar
        ]);

        echo json_encode($result);
        exit;
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode([
            'status'  => 'error',
            'message' => 'Error interno enviando confirmación',
            'error'   => $e->getMessage(),
        ]);
        exit;
    }
}
