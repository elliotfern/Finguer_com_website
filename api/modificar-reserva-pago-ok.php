<?php
/*
 * BACKEND LIBRARY
 * FUNCIONS UPDATE BOOK
 * @update_book_ajax
 */
  // envio confirmacion cliente:
  $importe = $_POST['importe'];

  function enviarCorreo($destinatario, $asunto, $mensaje, $bcc) {
    // Cabeceras adicionales
    $cabeceras = 'From: hello@finguer.com' . "\r\n" .
                 'Reply-To: hello@finguer.com' . "\r\n" .
                 'Bcc: ' . $bcc . "\r\n" .
                 'X-Mailer: PHP/' . phpversion();

    // Envío del correo
    mail($destinatario, $asunto, $mensaje, $cabeceras);
  }

    // Detalles del correo para avisar al cliente - confirmacion de compra
    $destinatario =  "hello@finguer.com";
    $asunto = "Nueva reserva en Finguer.com";
    $mensaje = "Acaba de entrar una nueva reserva en el sistema. Si el sistema verifica correctamente el pago, se mandará email de confirmación al cliente en 5 minutos.";

    // Envío del correo
    enviarCorreo($destinatario, $asunto, $mensaje, $bcc);

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

  
