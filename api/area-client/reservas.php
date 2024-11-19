<?php
global $conn;

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

// 1) Llistat reserves (pendientes)
if ( isset($_GET['type'], $_GET['cliente']) && $_GET['type'] === 'reservas' &&  $_GET['cliente']) {

    $email = $_GET['cliente'];

    $query = "
        SELECT 
            rc1.idReserva,
            rc1.fechaReserva,
            rc1.firstName AS clientNom,
            rc1.lastName AS clientCognom,
            rc1.tel AS telefono,
            rc1.diaSalida AS dataSortida,
            rc1.horaEntrada AS HoraEntrada,
            rc1.horaSalida AS HoraSortida,
            rc1.diaEntrada AS dataEntrada,
            rc1.matricula,
            rc1.vehiculo AS modelo,
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
        FROM 
            reserves_parking AS rc1
        LEFT JOIN 
            usuaris AS u ON rc1.idClient = u.id
        WHERE 
            u.email = :param
        ORDER BY 
            rc1.diaEntrada ASC, rc1.horaEntrada ASC
    ";

    // Preparar la consulta
    $stmt = $conn->prepare($query);

    // Vincular los parámetros
    $stmt->bindParam(':param', $email, PDO::PARAM_STR);

    // Ejecutar la consulta
    $stmt->execute();

    // Verificar si se encontraron resultados
    if ($stmt->rowCount() === 0) {
        echo json_encode(['error' => 'No rows found']);
        exit();
    }

    // Recopilar los resultados
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();

} if (isset($_GET['type'], $_GET['cliente']) && $_GET['type'] === 'factura' && is_numeric($_GET['cliente']) ) {

    $id = (int) $_GET['cliente'];
    require_once APP_ROOT . '/public/includes/funcions.php';

    echo enviarFactura($id);

    // Recopilar los resultados
    $data = "Factura enviada";

    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();
}
// Si llega aquí, es porque el endpoint solicitado no es válido
header('HTTP/1.1 400 Bad Request');
echo json_encode(['error' => 'Invalid request']);
exit();
?>
