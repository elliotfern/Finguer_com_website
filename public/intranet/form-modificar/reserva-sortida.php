<?php
global $conn;

$id = $routeParams[0];

/*
AQUESTA PÀGINA SERVEIX PER MODIFICAR SALIDA (DIA I HORA) DE LA RESERVA
UPDATE A LA TAULA: reserves_parking
COLUMNA: horaSalida, diaSalida
*/

if (is_numeric($id)) {
    $id_old =  intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.horaSalida, r.diaSalida
        FROM reserves_parking AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $horaSalida_old = $row['horaSalida'];
            $diaSalida_old = $row['diaSalida'];
            $idReserva_old = $row['idReserva'];
        }
            echo "<div class='container'>
            <h2>Canvi dia / hora de sortida del parking</h2>";
            
            if ($idReserva_old == 1) {
                echo "<h3>Canvi dades sortida (Dia i Hora). Reserva client anual amb ID: ".$id_old."</h3>";
            } else {
                echo '<h3>Canvi dades sortida (Dia i Hora). Reserva núm. '.$idReserva_old.'</h3>';
            }
          
              if (isset($_POST["update-sortida"])) {          
                  if (empty($_POST["horaSalida"])) {
                    $horaSalida = data_input($_POST["horaSalida"], ENT_NOQUOTES);
                  } else {
                    $horaSalida = data_input($_POST["horaSalida"], ENT_NOQUOTES);
                  }

                  if (empty($_POST["diaSalida"])) {
                    $diaSalida = data_input($_POST["diaSalida"], ENT_NOQUOTES);
                  } else {
                    $diaSalida = data_input($_POST["diaSalida"], ENT_NOQUOTES);
                  }
          
          
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET horaSalida=:horaSalida, diaSalida=:diaSalida
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":horaSalida", $horaSalida, PDO::PARAM_STR);
                    $stmt->bindParam(":diaSalida", $diaSalida, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;

                        if ($idReserva_old == 1) {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio dades sortida reserva - Dia/Hora";
                            $message = "Avis de modificacio de les dades sortida dia/hora de client ABONAMENT ANUAL";
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                            
                        } else {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio dades sortida reserva - Dia/Hora";
                            $message = "Avis de modificacio de les dades sortida dia/hora associat al client: ".$firstName_old." ".$lastName_old." amb num de comanda: " . $idReserva_old;
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                        }

                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Dades actualizades correctament.</h4></strong>';
                    echo "S'ha actualitzat correctament.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-sortida" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Dia sortida:</label>';
                    echo '<input type="date" class="form-control" id="diaSalida" name="diaSalida" value="'.htmlspecialchars_decode($diaSalida_old, ENT_QUOTES).'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Hora sortida:</label>';
                    echo '<input type="text" class="form-control" id="horaSalida" name="horaSalida" value="'.htmlspecialchars_decode($horaSalida_old, ENT_QUOTES).'">';
                    echo '</div>';
       
                    echo "<div class='md-12'>";
                    echo "<button id='update-sortida' name='update-sortida' type='submit' class='btn btn-primary'>Actualizar</button><a href='".APP_SERVER."/reserva/modificar/sortida/".$id_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'"/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }
           
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";
?>

