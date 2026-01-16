<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// Método
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$type = (string)($_GET['type'] ?? '');

try {

    // =========================================================
    // type=user  -> datos del usuario autenticado (por token)
    // =========================================================
    if ($type === 'user') {

        $user = auth_user();
        if ($user === null) {
            http_response_code(403);
            echo json_encode(['status' => 'error', 'message' => 'Access not allowed']);
            exit;
        }

        // Si quieres además leer datos frescos de BD (opcional):
        // global $conn;
        // $stmt = $conn->prepare("SELECT nombre, email, tipo_rol FROM usuarios WHERE uuid = :u LIMIT 1");
        // $stmt->bindValue(':u', $user['uuid_bin'], PDO::PARAM_LOB);
        // $stmt->execute();
        // $row = $stmt->fetch(PDO::FETCH_ASSOC);

        echo json_encode([
            'status' => 'success',
            'data' => [
                'uuid' => $user['uuid'],
                'role' => $user['role'],
                'name' => $user['name'],
                // 'email' => $row['email'] ?? null, // si lo consultas
            ],
        ]);
        exit;
    }

    // =========================================================
    // type=logout  -> borrar cookie token (multi-subdominio)
    // =========================================================
    if ($type === 'logout') {

        clearAuthCookies();

        http_response_code(200);
        echo json_encode(['status' => 'success', 'message' => 'Sessió tancada correctament.']);
        exit;
    }

    // =========================================================
    // type inválido
    // =========================================================
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Bad type',
        'allowed' => ['user', 'logout'],
    ]);
    exit;
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error',
        'details' => $e->getMessage(),
    ]);
    exit;
}
