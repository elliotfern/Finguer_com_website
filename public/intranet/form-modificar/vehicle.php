<?php
global $conn;

$id = $routeParams[0];

/*
AQUESTA PÀGINA SERVEIX PER MODIFICAR EL COTXE I MATRICULA DEL CLIENT
UPDATE A LA TAULA: reserves_parking
COLUMNA: vehiculo, matricula
*/

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.matricula, r.vehiculo, r.firstName, r.lastName
        FROM reserves_parking AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva_old = $row['idReserva'];
            $matricula_old = $row['matricula'];
            $vehiculo_old = $row['vehiculo'];
            $firstName_old = $row['firstName'];
            $lastName_old = $row['lastName'];
        }
            echo "<div class='container'>
            <h2>Canvi matrícula vehicle</h2>";
            
            if ($idReserva_old == 1) {
                echo "<h3>Reserva client anual amb ID: ".$id_old."</h3>";
            } else {
                echo '<h3>Reserva núm. '.$idReserva_old.'</h3>';
            }
          
              if (isset($_POST["update-matricula"])) {
                global $pdo_conn;
          
                  if (empty($_POST["matricula"])) {
                    $matricula = data_input($_POST["matricula"], ENT_NOQUOTES);
                  } else {
                    $matricula = data_input($_POST["matricula"], ENT_NOQUOTES);
                  }

                  if (empty($_POST["vehiculo"])) {
                    $vehiculo = data_input($_POST["vehiculo"], ENT_NOQUOTES);
                  } else {
                    $vehiculo = data_input($_POST["vehiculo"], ENT_NOQUOTES);
                  }
          
          
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET matricula=:matricula, vehiculo=:vehiculo
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
                    $stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;

                        if ($idReserva_old == 1) {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio cotxe CLIENT ABONAMENT ANUAL";
                            $message = "Avis de modificacio del cotxe/matricula de client ABONAMENT ANUAL";
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                        } else {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio cotxe client";
                            $message = "Avis de modificacio del cotxe/matricula de client: ".$firstName_old." ".$lastName_old." associat a num. de comanda: " . $idReserva_old;
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                        }

                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Matrícula actualizada correctament.</h4></strong>';
                    echo "La matrícula o model cotxe actualitzat és: ".$_POST["matricula"]."</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-matricula" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Model vehicle:</label>';
                    echo '<input type="text" class="form-control" id="vehiculo" name="vehiculo" value="'.htmlspecialchars_decode($vehiculo_old, ENT_QUOTES).'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Número de matrícula:</label>';
                    echo '<input type="text" class="form-control" id="matricula" name="matricula" value="'.htmlspecialchars_decode($matricula_old, ENT_QUOTES).'">';
                    echo '</div>';
       
                    echo "<div class='md-12'>";
                    echo "<button id='update-matricula' name='update-matricula' type='submit' class='btn btn-primary'>Actualizar</button><a href='".APP_SERVER."/reserva/modificar/vehicle/".$id_old."'></a>
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