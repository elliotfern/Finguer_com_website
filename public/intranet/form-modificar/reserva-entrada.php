<?php
global $conn;

$id = $routeParams[0];

/*
AQUESTA PÀGINA SERVEIX PER MODIFICAR ENTRADA (DIA I HORA) DE LA RESERVA
UPDATE A LA TAULA: reserves_parking
COLUMNA: horaEntrada, diaEntrada
*/

if (is_numeric($id)) {
    $id_old = intval($id);

    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.horaEntrada, r.diaEntrada, r.firstName, r.lastName
        FROM reserves_parking AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $horaEntrada_old = $row['horaEntrada'];
            $diaEntrada_old = $row['diaEntrada'];
            $idReserva_old = $row['idReserva'];
        }
    
        echo "<div class='container'>
        <h2>Canvi dia / hora d'entrada al parking</h2>";
            
            if ($idReserva_old == 1) {
                echo "<h3>Canvi dades entrada (Dia i Hora). Reserva client anual amb ID: ".$id_old."</h3>";
            } else {
                echo '<h3>Canvi dades entrada (Dia i Hora). Reserva núm. '.$idReserva_old.'</h3>';
            }
          
              if (isset($_POST["update-entrada"])) {
          
                  if (empty($_POST["horaEntrada"])) {
                    $horaEntrada = data_input($_POST["horaEntrada"], ENT_NOQUOTES);
                  } else {
                    $horaEntrada = data_input($_POST["horaEntrada"], ENT_NOQUOTES);
                  }

                  if (empty($_POST["diaEntrada"])) {
                    $diaEntrada = data_input($_POST["diaEntrada"], ENT_NOQUOTES);
                  } else {
                    $diaEntrada = data_input($_POST["diaEntrada"], ENT_NOQUOTES);
                  }
          
          
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET horaEntrada=:horaEntrada, diaEntrada=:diaEntrada
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":horaEntrada", $horaEntrada, PDO::PARAM_STR);
                    $stmt->bindParam(":diaEntrada", $diaEntrada, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;

                        if ($idReserva_old == 1) {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio dades entrada reserva - Dia/Hora";
                            $message = "Avis de modificacio de les dades entrada dia/hora associat al client ABONAMENT ANUAL";
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);

                        } else {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio dades entrada reserva - Dia/Hora";
                            $message = "Avis de modificacio de les dades entrada dia/hora associat a la comanda num.: " .$idReserva_old;
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
                    echo '<form action="" method="post" id="update-entrada" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Dia entrada:</label>';
                    echo '<input type="date" class="form-control" id="diaEntrada" name="diaEntrada" value="'.htmlspecialchars_decode($diaEntrada_old, ENT_QUOTES).'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Hora entrada:</label>';
                    echo '<input type="text" class="form-control" id="horaEntrada" name="horaEntrada" value="'.htmlspecialchars_decode($horaEntrada_old, ENT_QUOTES).'">';
                    echo '</div>';
       
                    echo "<div class='md-12'>";
                    echo "<button id='update-entrada' name='update-entrada' type='submit' class='btn btn-primary'>Actualizar</button><a href='".APP_SERVER."/reserva/modificar/entrada/".$id_old."'></a>
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

