<?php
// Configuración de cabeceras para aceptar JSON y responder JSON
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *"); // Permitir acceso desde cualquier origen (opcional, según el caso)
header("Access-Control-Allow-Methods: POST");

// Leer el cuerpo de la solicitud JSON
$data = json_decode(file_get_contents("php://input"), true);

// Verificar que los datos se recibieron correctamente
if (!$data) {
    echo json_encode([
        "status" => "error",
        "message" => "No se enviaron datos válidos.",
        "errors" => []
    ]);
    exit;
}

$errors = [];

// Validar y sanitizar datos recibidos
$hasError = false;

// validar camps obligatoris
// Validar nombre
if (empty($data["nombre"])) {
    $errors["nombre"] = "El nombre es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[a-zA-ZÀ-ÿ\s]+$/", $data["nombre"])) {
    $errors["nombre"] = "El nombre debe contener solo letras y espacios.";
    $hasError = true;
}

// Validar email
if (empty($data["email"])) {
    $errors["email"] = "El correo electrónico es obligatorio.";
    $hasError = true;
} elseif (!filter_var($data["email"], FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "El correo electrónico no es válido.";
    $hasError = true;
}

// Validar teléfono
if (empty($data["telefono"])) {
    $errors["telefono"] = "El teléfono es obligatorio.";
    $hasError = true;
} elseif (!preg_match("/^[0-9]{9,15}$/", $data["telefono"])) { 
    $errors["telefono"] = "El teléfono debe contener solo números y tener entre 9 y 15 dígitos.";
    $hasError = true;
}

// Si hay errores, enviarlos al cliente
if (!empty($errors)) {
    echo json_encode([
        "status" => "error",
        "message" => "Errores en los datos enviados.",
        "errors" => $errors
    ]);
    exit;
}

// si no hi ha errors, continuem amb la validacio de les dades

$nombre = data_input($data["nombre"]);
$email = data_input($data["email"]);
$telefono = data_input($data["telefono"]);

$empresa = !empty($data["empresa"]) ? data_input($data["empresa"]) : null;
$nif = !empty($data["nif"]) ? data_input($data["nif"]) : null;
$direccion = !empty($data["direccion"]) ? data_input($data["direccion"]) : null;
$ciudad = !empty($data["ciudad"]) ? data_input($data["ciudad"]) : null;
$codigo_postal = !empty($data["codigo_postal"]) ? data_input($data["codigo_postal"]) : null;
$pais = !empty($data["pais"]) ? data_input($data["pais"]) : null;

$tipoUsuario = 2; // Asignar tipo de usuario por defecto

// Si hay errores en los datos, devolver una respuesta de error
if ($hasError) {
    echo json_encode([
        "status" => "error",
        "message" => "Datos incompletos."
    ]);
    exit;
}

      global $conn;
      $sql = "INSERT INTO usuaris SET nombre=:nombre, email=:email, empresa=:empresa, nif=:nif, direccion=:direccion, ciudad=:ciudad, codigo_postal=:codigo_postal, pais=:pais, telefono=:telefono, tipoUsuario=:tipoUsuario";
      $stmt= $conn->prepare($sql);
      $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
      $stmt->bindParam(":email", $email, PDO::PARAM_STR);
      $stmt->bindParam(":empresa", $empresa, PDO::PARAM_STR);
      $stmt->bindParam(":nif", $nif, PDO::PARAM_STR);
      $stmt->bindParam(":direccion", $direccion, PDO::PARAM_STR);
      $stmt->bindParam(":ciudad", $ciudad, PDO::PARAM_STR);
      $stmt->bindParam(":codigo_postal", $codigo_postal, PDO::PARAM_STR);
      $stmt->bindParam(":pais", $pais, PDO::PARAM_STR);
      $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
      $stmt->bindParam(":tipoUsuario", $tipoUsuario, PDO::PARAM_INT);

      if ($stmt->execute()) {
        // Obtener el ID del nuevo cliente insertado
        $idCliente = $conn->lastInsertId();
        
        // response output
         // Devolver respuesta de éxito
        header( "Content-Type: application/json" );
        echo json_encode([
            "status" => "success",
            "idCliente" => $idCliente,
            "message" => "Cliente creado con exito."
        ]);

      } else {
          // response output - data error
          header( "Content-Type: application/json" );
          echo json_encode([
            "status" => "error",
            "message" => "Error en la base de datos."
        ]);
      }
  