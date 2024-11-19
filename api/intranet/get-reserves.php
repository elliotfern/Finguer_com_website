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
            // 1) Llistat reserves (pendientes)
            if (isset($_GET['type']) && $_GET['type'] == 'pendents') {
                $data = array();
                global $conn;
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tel AS 'telefono',
                        rc1.diaSalida AS 'dataSortida',
                        rc1.horaEntrada AS 'HoraEntrada',
                        rc1.horaSalida AS 'HoraSortida',
                        rc1.diaEntrada AS 'dataEntrada',
                        rc1.matricula,
                        rc1.vehiculo AS 'modelo',
                        rc1.vuelo,
                        rc1.tipo,
                        rc1.checkIn,
                        rc1.checkOut,
                        rc1.notes,
                        rc1.buscadores,
                        rc1.limpieza,
                        rc1.importe,
                        rc1.id,
                        rc1.processed,
                        u.nombre,
                        u.telefono AS tel
                        FROM reserves_parking AS rc1
                        LEFT JOIN reservas_buscadores AS b ON rc1.buscadores = b.id
                        LEFT JOIN usuaris AS u ON rc1.idClient = u.id
                        WHERE rc1.checkIn = 5
                        ORDER BY rc1.diaEntrada ASC, rc1.horaEntrada ASC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 2) Llistat reserves totals
            elseif (isset($_GET['type']) && $_GET['type'] == 'reserves') {
                $data = array();
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tel AS 'telefono',
                        rc1.diaSalida AS 'dataSortida',
                        rc1.horaEntrada AS 'HoraEntrada',
                        rc1.horaSalida AS 'HoraSortida',
                        rc1.diaEntrada AS 'dataEntrada',
                        rc1.matricula,
                        rc1.vehiculo AS 'modelo',
                        rc1.vuelo,
                        rc1.tipo,
                        rc1.checkIn,
                        rc1.checkOut,
                        rc1.notes,
                        rc1.buscadores,
                        rc1.limpieza,
                        rc1.importe,
                        rc1.id,
                        rc1.processed,
                        u.nombre,
                        u.telefono AS tel
                        FROM reserves_parking AS rc1
                        LEFT JOIN usuaris AS u ON rc1.idClient = u.id
                        WHERE rc1.checkIn = 5 OR rc1.checkIn = 1
                        ORDER BY rc1.id DESC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 3) Numero reserves pendents
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesPendents') {
                $data = array();
                $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
                        FROM reserves_parking as r
                        WHERE r.checkIn = 5");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 4) Reserves al parking
            elseif (isset($_GET['type']) && $_GET['type'] == 'parking') {
                $data = array();
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tel AS 'telefono',
                        rc1.diaSalida AS 'dataSortida',
                        rc1.horaEntrada AS 'HoraEntrada',
                        rc1.horaSalida AS 'HoraSortida',
                        rc1.diaEntrada AS 'dataEntrada',
                        rc1.matricula AS 'matricula',
                        rc1.vehiculo AS 'modelo',
                        rc1.vuelo,
                        rc1.tipo,
                        rc1.checkIn,
                        rc1.checkOut,
                        rc1.notes,
                        rc1.buscadores,
                        rc1.limpieza,
                        rc1.id,
                        rc1.importe,
                        rc1.processed,
                        u.nombre,
                        u.telefono AS tel
                        FROM reserves_parking AS rc1
                        LEFT JOIN reservas_buscadores AS b ON rc1.buscadores = b.id
                        LEFT JOIN usuaris AS u ON rc1.idClient = u.id
                        WHERE rc1.checkIn = 1
                        GROUP BY rc1.id
                        ORDER BY rc1.diaSalida ASC, rc1.horaSalida ASC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 5) Numero reserves parking
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesParking') {
                $data = array();
                $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
                        FROM reserves_parking as r
                        WHERE r.checkIn = 1");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 6) Reserves completades
            elseif (isset($_GET['type']) && $_GET['type'] == 'completades') {
                $data = array();
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tipo,
                        rc1.id,
                        rc1.importe,
                        c.nombre
                        FROM reserves_parking AS rc1
                        LEFT JOIN reservas_buscadores AS b ON rc1.buscadores = b.id
                        LEFT JOIN usuaris AS c ON rc1.idClient = c.id
                        WHERE rc1.checkOut = 2
                        GROUP BY rc1.id
                        ORDER BY rc1.id DESC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 7) Numero reserves completades
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesCompletades') {
                $data = array();
                $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
                        FROM reserves_parking as r
                        WHERE r.checkOut = 2");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');
                    
                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
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
?>
