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
    if (empty($_POST["nombre"])) {
        $hasError = true;
    } else {
        $nombre = data_input($_POST["nombre"], ENT_NOQUOTES);
    }

    if (empty($_POST["email"])) {
        $hasError = true;
    } else {
        $email = data_input($_POST["email"], ENT_NOQUOTES);
    }

    if (empty($_POST["empresa"])) {
        $empresa = NULL;
      } else {
        $empresa = data_input($_POST["empresa"], ENT_NOQUOTES);
      }

    if (empty($_POST["nif"])) {
        $nif = NULL;
    } else {
        $nif = data_input($_POST["nif"], ENT_NOQUOTES);
    }

    if (empty($_POST["direccion"])) {
        $direccion = NULL;
    } else {
        $direccion = data_input($_POST["direccion"], ENT_NOQUOTES);
    }

    if (empty($_POST["ciudad"])) {
        $ciudad = NULL;
    } else {
        $ciudad = data_input($_POST["ciudad"], ENT_NOQUOTES);
    }

    if (empty($_POST["codigo_postal"])) {
        $codigo_postal = NULL;
    } else {
        $codigo_postal = data_input($_POST["codigo_postal"], ENT_NOQUOTES);
    }

    if (empty($_POST["pais"])) {
        $pais = NULL;
    } else {
        $pais = data_input($_POST["pais"], ENT_NOQUOTES);
    }

    if (empty($_POST["telefono"])) {
        $telefono = NULL;
    } else {
        $telefono = data_input($_POST["telefono"], ENT_NOQUOTES);
    }
   
    if (!isset($hasError)) {
      global $conn;
      $sql = "INSERT INTO usuaris SET nombre=:nombre, email=:email, empresa=:empresa, nif=:nif, direccion=:direccion, ciudad=:ciudad, codigo_postal=:codigo_postal, pais=:pais, telefono=:telefono";
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

      if ($stmt->execute()) {
        // Obtener el ID del nuevo cliente insertado
        $id_cliente = $conn->lastInsertId();
        
        // response output
        $response['status'] = "success";
        $response['idCliente'] = $id_cliente;

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