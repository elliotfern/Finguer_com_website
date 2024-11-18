<?php
/*
 * BACKEND FINGUER
 * INSERIR NOU CLIENT
 * taula: usuaris
 */

 // insert data to db
    if (empty($_POST["idClient"])) {
        $hasError = true;
    } else {
        $idClient = data_input($_POST["idClient"], ENT_NOQUOTES);
    }

    if (empty($_POST["idReserva"])) {
        $hasError = true;
    } else {
        $idReserva = data_input($_POST["idReserva"], ENT_NOQUOTES);
    }

    if (empty($_POST["tipo"])) {
        $hasError = true;
      } else {
        $tipo = data_input($_POST["tipo"], ENT_NOQUOTES);
      }

    if (empty($_POST["horaEntrada"])) {
        $hasError = true;
    } else {
        $horaEntrada = data_input($_POST["horaEntrada"], ENT_NOQUOTES);
    }  

    if (empty($_POST["horaSalida"])) {
        $hasError = true;
    } else {
        $horaSalida = data_input($_POST["horaSalida"], ENT_NOQUOTES);
    }


    if (empty($_POST["vehiculo"])) {
        $vehiculo = NULL;
    } else {
        $vehiculo = data_input($_POST["vehiculo"], ENT_NOQUOTES);
    }

    if (empty($_POST["matricula"])) {
        $matricula = NULL;
    } else {
        $matricula = data_input($_POST["matricula"], ENT_NOQUOTES);
    }

    if (empty($_POST["vuelo"])) {
        $vuelo = NULL;
    } else {
        $vuelo = data_input($_POST["vuelo"], ENT_NOQUOTES);
    }

    $limpieza = $_POST["limpieza"];
    $processed = $_POST["processed"];

    $diaEntrada2 = $_POST["diaEntrada"]; // Suponiendo que $_POST["diaSalida"] contiene la fecha en formato "DD-MM-YYYY"
    $fecha_objeto = DateTime::createFromFormat("Y-d-m", $diaEntrada2);
    $diaEntrada = $fecha_objeto->format("Y-m-d");

    $diaSalida2 = $_POST["diaSalida"];
    $fecha_objeto2 = DateTime::createFromFormat("Y-d-m", $diaSalida2);
    $diaSalida = $fecha_objeto2->format("Y-m-d");
    $checkIn = 5;
    $fechaReserva = date("Y-m-d H:i:s");
    $seguroCancelacion = $_POST["cancelacion"];

    $costeReserva = isset($_POST['costeReserva']) ? $_POST['costeReserva'] : 0;
    $costeLimpieza = isset($_POST['costeLimpieza']) ? $_POST['costeLimpieza'] : 0;
    $costeSubTotal = isset($_POST['costeSubTotal']) ? $_POST['costeSubTotal'] : 0;
    $costeIva = isset($_POST['costeIva']) ? $_POST['costeIva'] : 0;
    $importe = isset($_POST['costeTotal']) ? $_POST['costeTotal'] : 0;
    $costeSeguro = isset($_POST['costeSeguro']) ? $_POST['costeSeguro'] : 0;
    
    if (!isset($hasError)) {
      global $conn;
      $sql = "INSERT INTO reserves_parking SET idClient=:idClient, idReserva=:idReserva, tipo=:tipo, horaEntrada=:horaEntrada, diaEntrada=:diaEntrada, horaSalida=:horaSalida, diaSalida=:diaSalida, vehiculo=:vehiculo, matricula=:matricula, vuelo=:vuelo, limpieza=:limpieza, processed=:processed, checkIn =:checkIn, fechaReserva=:fechaReserva, seguroCancelacion=:seguroCancelacion, importe=:importe, subTotal=:subTotal, importeIva=:importeIva, costeReserva=:costeReserva, costeSeguro=:costeSeguro, costeLimpieza=:costeLimpieza";
      $stmt= $conn->prepare($sql);
      $stmt->bindParam(":idClient", $idClient, PDO::PARAM_STR);
      $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_STR);
      $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
      $stmt->bindParam(":horaEntrada", $horaEntrada, PDO::PARAM_STR);
      $stmt->bindParam(":diaEntrada", $diaEntrada, PDO::PARAM_STR);
      $stmt->bindParam(":horaSalida", $horaSalida, PDO::PARAM_STR);
      $stmt->bindParam(":diaSalida", $diaSalida, PDO::PARAM_STR);
      $stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
      $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
      $stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
      $stmt->bindParam(":limpieza", $limpieza, PDO::PARAM_STR);
      $stmt->bindParam(":processed", $processed, PDO::PARAM_STR);
      $stmt->bindParam(":checkIn", $checkIn, PDO::PARAM_STR);
      $stmt->bindParam(":fechaReserva", $fechaReserva, PDO::PARAM_STR);
      $stmt->bindParam(":seguroCancelacion", $seguroCancelacion, PDO::PARAM_INT);
      $stmt->bindParam(":importe", $importe, PDO::PARAM_STR);
      $stmt->bindParam(":subTotal", $costeSubTotal, PDO::PARAM_STR);
      $stmt->bindParam(":importeIva", $costeIva, PDO::PARAM_STR);
      $stmt->bindParam(":costeReserva", $costeReserva, PDO::PARAM_STR);
      $stmt->bindParam(":costeSeguro", $costeSeguro, PDO::PARAM_STR);
      $stmt->bindParam(":costeLimpieza", $costeLimpieza, PDO::PARAM_STR);

      if ($stmt->execute()) {      
        // response output
        $response['status'] = "success";

        header( "Content-Type: application/json" );
        echo json_encode($response);

      } else {
        // response output - data error
        $response['status'] = 'error';

        header( "Content-Type: application/json" );
        echo json_encode($response);
      }
    } else {
      // response output - data error
      $response['status'] = 'error';
      header( "Content-Type: application/json" );
      echo json_encode($response);
    } 