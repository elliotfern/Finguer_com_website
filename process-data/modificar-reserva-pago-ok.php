<?php
/*
 * BACKEND LIBRARY
 * FUNCIONS UPDATE BOOK
 * @update_book_ajax
 */

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

  
