<?php
// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permitir acceso desde cualquier origen (opcional, según el caso)
header("Access-Control-Allow-Methods: GET");

if (isset($_GET['session'])) {
    $session = $_GET['session'];

    global $conn;
    /** @var PDO $conn */
    $stmt = $conn->prepare("SELECT
	id, precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, fechaEntrada, fechaSalida, horaEntrada,	horaSalida,limpieza, tipoReserva, diasReserva, seguroCancelacion
            FROM carro_compra
            WHERE session = :session");

    $stmt->execute(['session' => $session]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(null);  // Devuelve un objeto JSON nulo si no hay resultados
    } else {
        // Solo obtenemos la primera fila ya que parece ser una búsqueda por ID
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($row);  // Codifica la fila como un objeto JSON
    }
}
