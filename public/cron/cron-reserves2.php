<?php
global $conn;

// Paso 2: Consulta SQL para seleccionar reservas de los últimos 5 minutos
$sql = "SELECT idReserva, fechaReserva, id
FROM reserves_parking
WHERE idReserva = 1114210846";
$stmt = $conn->prepare($sql);
$stmt->execute();

// Paso 3: Si hay reservas nuevas, ejecutar acciones
if ($stmt->rowCount() > 0) {
    // Obtener los resultados de la consulta
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $id = $row["id"];
        // Aquí puedes ejecutar tus acciones, como enviar un correo electrónico y verificar el pago con Redsys
        verificarPagament($id);

    }
} else {
    echo "No hay nuevas reservas en los últimos 2 minutos";
}

?>