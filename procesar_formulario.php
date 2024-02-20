<?php
session_start();

// Verificar si se han enviado datos a través de POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Capturar los datos del formulario
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];

    // Guardar los datos en la sesión
    $_SESSION['nombre'] = $nombre;
    $_SESSION['email'] = $email;
    
    // Responder con algún mensaje si es necesario
    echo "Datos recibidos y guardados en la sesión correctamente.";
}
?>
