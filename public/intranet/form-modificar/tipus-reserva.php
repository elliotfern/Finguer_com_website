<?php
global $conn;

$id = $routeParams[0];

/*
AQUESTA PÀGINA SERVEIX PER MODIFICAR EL TIPUS DE RESERVA
UPDATE A LA TAULA: reserves_parking
COLUMNA: tipo
resultat: 1 -> Finguer Class/ 2-> Gold Finguer Class
*/

if (is_numeric($id)) {
    $id_old =  intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.tipo, r.firstName, r.lastName
        FROM reserves_parking AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva_old = $row['idReserva'];
            $tipo_old = $row['tipo'];
            $firstName_old = $row['firstName'];
            $lastName_old = $row['lastName'];
        }
            echo "<div class='container'>
            <h2>Canvi tipus de reserva</h2>";
            echo "<h3>Reserva núm: ".$idReserva_old."</h3>";

              if (isset($_POST["update-tipus"])) {
          
                  if (empty($_POST["tipo"])) {
                    $tipo = data_input($_POST["tipo"], ENT_NOQUOTES);
                  } else {
                    $tipo = data_input($_POST["tipo"], ENT_NOQUOTES);
                  }
          
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    global $conn;
                    $sql = "UPDATE reserves_parking SET tipo=:tipo
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;

                        if ($idReserva_old == 1) {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio reserva eina treballadors";
                            $message = "Avis de modificacio de tipus de reserva de client ABONAMENT ANUAL";
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                            
                        } else {
                            $to = "hello@finguer.com";
                            $subject = "Modificacio reserva eina treballadors";
                            $message = "Avis de modificacio de tipus de reserva del client: ".$firstName_old." ".$lastName_old." amb num. de comanda: " .$idReserva_old;
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                        }

                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Reserva actualizada correctament.</h4></strong>';
                    echo "La reserva actualizada és: ".$idReserva_old."</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-tipus" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Tipus de reserva:</label>';
                    echo '<select class="form-select" name="tipo" id="tipo">';
                    echo '<option selected disabled>Selecciona una opció:</option>';
                    
                    if ($tipo_old == 1) {
                        echo "<option value='1' selected>Finguer Class</option>";
                        echo "<option value='2'>Gold Finguer</option>"; 
                      } else {
                        echo "<option value='1'>Finguer Class</option>";
                        echo "<option value='2' selected>Gold Finguer Class</option>";
                      }
                    echo '</select>';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='update-tipus' name='update-tipus' type='submit' class='btn btn-primary'>Actualizar</button><a href='".APP_SERVER."/reserva/modificar/tipus/".$id_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }
           
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap reserva.";
}

echo "</div>";
?>

