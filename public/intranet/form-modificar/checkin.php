<?php
global $conn;

$id = $routeParams[0];

echo "<div class='container'>";

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.checkIn, r.id, r.idReserva
        FROM reserves_parking AS r
        WHERE r.id=$id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $checkIn_old = $row['checkIn'];
            $idReserva_old = $row['idReserva'];
        }

        if ($checkIn_old == 5) {
            $estat = "Reserva registrada al sistema pendent de fer check-In";
        }
            if ($idReserva_old == 1) {
                echo "<h2>Fer Check-In de reserva client anual amb ID: ".$id_old."</h2>";
            } else {
                echo "<h2>Fer Check-In de reserva núm: ".$idReserva_old."</h2>";
                echo '<h4>Estat actual: '.$estat.'</h4>';
            }
            
              if (isset($_POST["add-checkin"])) {
                global $pdo_conn;
                  
                $checkIn = 1;
                  
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET checkIn=:checkIn
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":checkIn", $checkIn, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Check-In enregistrat correctament.</h4></strong>';
                    echo "Check-In realizat.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="checkin" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<input type="hidden" id="checkIn" name="checkIn" value="1">';

                    echo "<p>Estàs segur que vols fer el CHECK-IN d'aquesta reserva?</p>";
        
                    echo "<div class='md-12'>";
                    echo "<button id='add-checkin' name='add-checkin' type='submit' class='btn btn-primary'>Fer check-In</button><a href='".APP_SERVER."/reserva/fer/check-in/".$id_old."'></a>
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
