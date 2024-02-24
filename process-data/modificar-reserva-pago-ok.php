<?php
/*
 * BACKEND LIBRARY
 * FUNCIONS UPDATE BOOK
 * @update_book_ajax
 */
  // envio confirmacion cliente:
  $email = $_POST['email_cliente'];

  function enviarCorreo($destinatario, $asunto, $mensaje) {
    // Cabeceras adicionales
    $cabeceras = 'From: hello@finguer.com' . "\r\n" .
                 'Reply-To: hello@finguer.com' . "\r\n" .
                 'X-Mailer: PHP/' . phpversion();

    // Envío del correo
    mail($destinatario, $asunto, $mensaje, $cabeceras);
  }

    // Detalles del correo para avisar al propietario del parking
    $destinatario =  $email;
    $asunto = "Confirmación de su reserva en Finguer.com";
    $mensaje = "Le confirmamos que hemos procesado correctamente la reserva que acaba de hacer en nuestra página web. Muchas gracias por su confianza";

    // Envío del correo
    enviarCorreo($destinatario, $asunto, $mensaje);

  global $conn;

    $idReserva = filter_input(INPUT_POST, 'idReserva', FILTER_SANITIZE_NUMBER_INT);
    $processed = 1;

    if (!isset($hasError)) {
    $sql = "UPDATE reserves_parking SET processed=:processed WHERE idReserva=:idReserva";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
    $stmt->bindParam(":processed", $processed, PDO::PARAM_INT);
    $stmt->execute();
 
        // response output
        $response['status'] = 'success';

        header( "Content-Type: application/json" );
        echo json_encode($response);
    } else {
      // response output - data error
      $response['status'] = 'error';

      header( "Content-Type: application/json" );
      echo json_encode($response);


    }

  
