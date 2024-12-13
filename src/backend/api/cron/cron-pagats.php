<?php
global $conn;

// Paso 2: Consulta SQL para seleccionar reservas de los últimos 5 minutos
$sql = "SELECT idReserva, fechaReserva, id
FROM reserves_parking
WHERE processed = 0 AND fechaReserva >= NOW() - INTERVAL 1 MONTH AND idReserva != 1;";
/** @var PDO $conn */
$stmt = $conn->prepare($sql);
$stmt->execute();

// Verificar si se encontraron resultados
if ($stmt->rowCount() === 0) {
    $data = [
        'status' => 'error',
        'message' => 'Actualmente no hay ninguna reserva que cumpla con las condiciones establecidas en la consulta.',
    ];
    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver los datos en formato JSON
    echo json_encode($data);
    exit();
}

// Inicializar un array para recopilar los errores
$errors = [];

// Obtener los resultados de la consulta
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = $row["id"];

    // Ejecutar la función y capturar el resultado
    $result = verificarPagament($id, true);

    // Verificar si $result es un array y contiene 'status'
    if (is_array($result) && isset($result['status']) && $result['status'] === 'error') {
        $errors[] = $result; // Almacenar el error en el array
    }
}

// Verificar si hay errores acumulados
if (!empty($errors)) {
    // Establecer el encabezado de respuesta a JSON
    header('Content-Type: application/json');

    // Devolver todos los errores en un único JSON
    echo json_encode(['errors' => $errors]);
    exit();
}

// Si no hay errores, devolver una respuesta exitosa
header('Content-Type: application/json');
echo json_encode([
    'status' => 'success',
    'message' => 'Todos los pagos se han verificado correctamente.',
]);
exit();
