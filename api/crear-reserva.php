<?php
/*
 * BACKEND FINGUER
 * INSERIR NOU CLIENT
 * taula: usuaris
 */
    function data_input($data) {
      $data = trim($data);
      $data = stripslashes($data);
      $data = htmlspecialchars($data);
      return $data;
    }
    
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
    $importe = $_POST["importe"];

    $diaEntrada2 = $_POST["diaEntrada"]; // Suponiendo que $_POST["diaSalida"] contiene la fecha en formato "DD-MM-YYYY"
    $fecha_objeto = DateTime::createFromFormat("Y-d-m", $diaEntrada2);
    $diaEntrada = $fecha_objeto->format("Y-m-d");


    $diaSalida2 = $_POST["diaSalida"];
    $fecha_objeto2 = DateTime::createFromFormat("Y-d-m", $diaSalida2);
    $diaSalida = $fecha_objeto2->format("Y-m-d");
    $checkIn = 5;
    $fechaReserva = date("Y-m-d H:i:s");
    $seguroCancelacion = $_POST["cancelacion"];
    
    if (!isset($hasError)) {
      global $conn;
      $sql = "INSERT INTO reserves_parking SET idClient=:idClient, idReserva=:idReserva, tipo=:tipo, horaEntrada=:horaEntrada, diaEntrada=:diaEntrada, horaSalida=:horaSalida, diaSalida=:diaSalida, vehiculo=:vehiculo, matricula=:matricula, vuelo=:vuelo, limpieza=:limpieza, processed=:processed, checkIn =:checkIn, fechaReserva=:fechaReserva, importe=:importe, seguroCancelacion=:seguroCancelacion";
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
      $stmt->bindParam(":importe", $importe, PDO::PARAM_STR);
      $stmt->bindParam(":seguroCancelacion", $seguroCancelacion, PDO::PARAM_INT);

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