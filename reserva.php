<?php
// Incluir archivo de conexión a la base de datos
//include 'includes/db.php';

// Recibir datos del formulario
$fecha_entrada = $_POST['fecha_entrada'];
$fecha_salida = $_POST['fecha_salida'];

// Calcular costo total de la reserva
$fecha_inicio = new DateTime($fecha_entrada);
$fecha_fin = new DateTime($fecha_salida);
$intervalo = $fecha_inicio->diff($fecha_fin);
$costo_total = $intervalo->days * 5; // 5 euros por día

// Guardar la reserva en la base de datos (implementación necesaria)

// Redirigir al usuario a la página de pago
header("Location: pagina_pago.php?costo_total=$costo_total");
exit();
?>
