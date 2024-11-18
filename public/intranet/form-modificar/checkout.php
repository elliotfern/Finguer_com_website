<?php
global $conn;

$id = $routeParams[0];

echo "<div class='container'>";

if (is_numeric($id)) {
    $id_old = intval($id);

    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.checkIn, r.idReserva
        FROM reserves_parking AS r
        WHERE r.id=$id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $checkIn_old = $row['checkIn'];
            $idReserva_old = $row['idReserva'];
        }

        if ($checkIn_old == 1) {
            $estat = "Reserva al parking pendent de check-Out";
        }

        if ($idReserva_old == 1) {
            echo "<h2>Fer Check-Out de reserva client anual amb ID: ".$id_old."</h2>";
        } else {
            echo "<h2>Fer Check-Out de reserva núm: ".$idReserva_old." </h2>";
            echo '<h4>Estat actual: '.$estat.'</h4>';
        }

          
              if (isset($_POST["add-checkout"])) {
                global $pdo_conn;
                  
                $checkOut = 2;
                $checkIn = 3;
                  
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET checkOut=:checkOut, checkIn=:checkIn
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":checkOut", $checkOut, PDO::PARAM_INT);
                    $stmt->bindParam(":checkIn", $checkIn, PDO::PARAM_INT);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Check-Out enregistrat correctament.</strong></h4>';
                    echo "Check-Out realizat.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="checkout" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<input type="hidden" id="checkOut" name="checkOut" value="2">';
                    echo '<input type="hidden" id="checkIn" name="checkIn" value="3">';

                    echo "<p>Estàs segur que vols fer el CHECK-OUT d'aquesta reserva i marcar-la com a completada?</p>";
        
                    echo "<div class='md-12'>";
                    echo "<button id='add-checkout' name='add-checkout' type='submit' class='btn btn-primary'>Fer check-Out</button><a href='".APP_SERVER."/reserva/fer/check-out/".$id_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    if ($idReserva_old == 1) {
                        echo '<a href="reserves-anuals-estat-parking.php" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                    } else {
                        echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                    }
                }
           
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";
?>
