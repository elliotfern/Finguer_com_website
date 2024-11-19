<?php

// Check if the request method is GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
} else {
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Verificar el token aquí según tus requerimientos
        if (validarToken($token)) {
            // Token válido, puedes continuar con el código para obtener los datos del usuario

            // 1) Llistat topics
            // ruta GET => "https://finguer.com/api/intranet/users/?type=user&701"
            if (isset($_GET['type']) && $_GET['type'] === 'user' && isset($_GET['id']) && is_numeric($_GET['id'])) {
                global $conn;
                $id = $_GET['id'];

                $query = "SELECT u.nombre
                FROM epgylzqu_finguer.usuaris AS u
                WHERE u.id = :param";

                // Preparar la consulta
                $stmt = $conn->prepare($query);

                // Vincular los parámetros
                $stmt->bindParam(':param', $id);
                    
                // Ejecutar la consulta
                $stmt->execute();
                
                // Verificar si se encontraron resultados
                if ($stmt->rowCount() === 0) {
                    echo json_encode(['error' => 'No rows found']);
                    exit;
                }

                // Recopilar los resultados
                $data = $stmt->fetch(PDO::FETCH_ASSOC);
                
                // Establecer el encabezado de respuesta a JSON
                header('Content-Type: application/json');
                
                // Devolver los datos en formato JSON
                echo json_encode($data);
            
                // 1) Llistat topics
            // ruta GET => "https://finguer.com/api/intranet/users/?type=user&701"
            } elseif (isset($_GET['type']) && $_GET['type'] === 'deleteCookies') {

                // Eliminar cookies seguras
                if (isset($_COOKIE['token'])) {
                    setcookie('token', '', time() - 3600, '/', '', true, true); 
                }

                // Eliminar otras cookies si es necesario
                setcookie('user_id', '', time() - 3600, '/', '', true, true); 

                // También puedes borrar las sesiones o realizar otras acciones relacionadas
                session_start();
                session_destroy(); // Si estás usando sesiones en PHP

                // Responder al cliente
                echo json_encode(['message' => 'Sesión cerrada correctamente.']);
                
            } else {
                // Si 'type', 'id' o 'token' están ausentes o 'type' no es 'user' en la URL
                header('HTTP/1.1 403 Forbidden');
                echo json_encode(['error' => 'Something get wrong']);
                exit();
            }

        } else {
        // Token no válido
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