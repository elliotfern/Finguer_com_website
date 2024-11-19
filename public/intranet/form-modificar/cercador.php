<?php require_once APP_ROOT . '/public/intranet/inc/header.php';?>

<?php
global $conn;

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.buscadores, r.id, r.idReserva
        FROM reserves_parking AS r
        WHERE r.id=$id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $buscadores_old = $row['buscadores'];
            $idReserva_old = $row['idReserva'];
        }

            echo "<div class='container'>
            <h2>Afegir buscador reserva número: ".$idReserva_old." </h2>";
          
              if (isset($_POST["update-buscadores"])) {
                global $pdo_conn;
                  
                if (empty($_POST["buscadores"])) {
                    $buscadores = data_input($_POST["buscadores"], ENT_NOQUOTES);
                  } else {
                    $buscadores = data_input($_POST["buscadores"], ENT_NOQUOTES);
                  }
                  
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET buscadores=:buscadores
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":buscadores", $buscadores, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Buscadores actualizat correctament.</strong></h4>';
                    echo "Buscadors actualizat.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-buscadores" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<h3>Vols afegir o modificar la reserva de buscador?:</h3>';
              
                    echo '<div class="col-md-4">';
                    echo '<label>Buscador:</label>';
                    echo '<select class="form-select" name="buscadores" id="buscadores">';
                    echo '<option selected disabled>Selecciona una opció:</option>';
                    $sql = "SELECT b.id, b.nombre
                    FROM reservas_buscadores AS b
                    ORDER BY b.nombre ASC";

                    $pdo_statement = $conn->prepare($sql);
                    $pdo_statement->execute();
                    $result = $pdo_statement->fetchAll();
                    foreach($result as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                            echo "<option value=".$id.">".$nombre."</option>"; 
                      }
                    echo '</select>';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='update-buscadores' name='update-buscadores' type='submit' class='btn btn-primary'>Alta buscador</button><a href='".APP_SERVER."/reserva/modificar/cercador/".$id_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }
           
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";
?>