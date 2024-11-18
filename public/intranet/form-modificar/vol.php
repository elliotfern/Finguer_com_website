<?php
global $conn;

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT pm.vuelo, pm.idReserva
        FROM reserves_parking AS pm
        WHERE pm.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $vuelo_old = $row['vuelo'];
            $idReserva_old = $row['idReserva'];
        }
            echo "<div class='container'>
            <h2>Canvi vol client</h2>";

            if ($idReserva_old == 1) {
                echo "<h3>Reserva client anual ID núm. '.$id_old.'</h3>";
            } else {
                echo '<h3>Reserva núm. '.$idReserva_old.'</h3>';
            }
          
              if (isset($_POST["update-vol"])) {
                global $pdo_conn;
          
                  if (empty($_POST["vuelo"])) {
                    $vuelo = data_input($_POST["vuelo"], ENT_NOQUOTES);
                  } else {
                    $vuelo = data_input($_POST["vuelo"], ENT_NOQUOTES);
                  }
          
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET vuelo=:vuelo
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Vol actualizat correctament.</h4></strong>';
                    echo "El vol actualitzat és: ".$_POST["vuelo"]."</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-vol" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Vol client:</label>';
                    echo '<input type="text" class="form-control" id="vuelo" name="vuelo" value="'.$vuelo_old.'">';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='update-vol' name='update-vol' type='submit' class='btn btn-primary'>Actualizar vol</button><a href='".APP_SERVER."/reserva/modificar/vol/".$id_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }
           
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vol.";
}

echo "</div>";
?>

