<?php

// Verificar si el método de la solicitud es GET
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
} else {
    // Verificar si el token está presente en las cookies
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Verificar el token aquí según tus requerimientos
        if (validarToken($token)) {

            // Endpoint: actualizar estado_vehiculo de una reserva
            if (isset($_GET['type']) && $_GET['type'] === 'update-estado') {
                header('Content-Type: application/json; charset=utf-8');
                global $conn;
                /** @var PDO $conn */

                // Leer JSON del body
                $input = json_decode(file_get_contents('php://input'), true);

                $id = isset($input['id']) ? (int)$input['id'] : 0;
                $nuevoEstado = $input['estado_vehiculo'] ?? null;

                $allowedEstados = ['pendiente_entrada', 'dentro', 'salido'];

                if ($id <= 0 || !in_array($nuevoEstado, $allowedEstados, true)) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Parámetros inválidos',
                    ]);
                    exit;
                }

                try {
                    $sql = "UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado_vehiculo = :estado_vehiculo
                WHERE id = :id";

                    $stmt = $conn->prepare($sql);
                    $stmt->bindValue(':estado_vehiculo', $nuevoEstado, PDO::PARAM_STR);
                    $stmt->bindValue(':id', $id, PDO::PARAM_INT);

                    $stmt->execute();

                    if ($stmt->rowCount() > 0) {
                        echo json_encode([
                            'success' => true,
                            'message' => 'Estado actualizado correctamente',
                            'id' => $id,
                            'estado_vehiculo' => $nuevoEstado,
                        ]);
                    } else {
                        // No filas afectadas: o no existe la reserva, o ya tenía ese estado
                        echo json_encode([
                            'success' => true,
                            'message' => 'Sin cambios (ya tenía este estado o no existe la reserva)',
                            'id' => $id,
                            'estado_vehiculo' => $nuevoEstado,
                        ]);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Error al actualizar el estado de la reserva',
                        'error' => $e->getMessage(),
                    ]);
                }

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
