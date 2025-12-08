<?php


// Verificar si el método de la solicitud es GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
} else {
    // Verificar si el token está presente en las cookies
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Verificar el token aquí según tus requerimientos
        if (validarToken($token)) {
            /* -------------------------------
            FACTURA PDF
            -------------------------------- */
            if (isset($_GET['type']) && $_GET['type'] === 'factura-pdf') {

                $idFactura = isset($_GET['id']) ? (int)$_GET['id'] : 0;

                if ($idFactura <= 0) {
                    http_response_code(400);
                    echo 'ID de factura no válido';
                    exit;
                }

                // Asegurarte que la función existe
                if (!function_exists('generarFacturaPdf')) {
                    http_response_code(500);
                    echo 'Funcion generar Pdf no disponible';
                    exit;
                }

                generarFacturaPdf($idFactura);
                exit;
            }
        } else {
            // Token inválido
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Invalid token']);
            exit();
        }
    } else {
        // No se proporcionó un token
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Access not allowed']);
        exit();
    }
}
