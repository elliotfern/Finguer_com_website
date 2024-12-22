<?php
global $conn;

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT u.nombre, u.telefono, u.id AS idClient
        FROM reserves_parking AS r
        LEFT JOIN usuaris AS u ON r.idClient = u.id
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $tel_old = $row['telefono'];
            $firstName_old = $row['nombre'];
            $idClient = $row['idClient'];
        }
            echo "<div class='container'>
            <h2>Canvi telèfon del client</h2>";
            echo "<h3>Client: ".$firstName_old." </h3>";

              if (isset($_POST["update-tel"])) {
                  if (empty($_POST["tel"])) {
                    $tel = data_input($_POST["tel"], ENT_NOQUOTES);
                  } else {
                    $tel = data_input($_POST["tel"], ENT_NOQUOTES);
                  }

               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    global $pdo_conn;
                    $sql = "UPDATE usuaris SET telefono=:telefono
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":telefono", $tel, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $idClient, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                            $to = "hello@finguer.com";
                            $subject = "Modificacio telefon client";
                            $message = "Avis de modificacio del telefon de client: $firstName_old ";
                            $from = "hello@finguer.com";
                            $headers = "De:" . $from;

                            mail($to,$subject,$message,$headers);
                            

                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Dades processades correctament!</h4></strong>';
                    echo "L'actualització s'ha realitzat correctament</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</h4></strong>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-tel" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<div class="col-md-4">';
                    echo '<label>Telèfon client:</label>';
                    echo '<input type="text" class="form-control" id="tel" name="tel" value="'.$tel_old.'">';
                    echo '</div>';
                        
                    echo "<div class='md-12'>";
                    echo "<button id='update-tel' name='update-tel' type='submit' class='btn btn-primary'>Actualizar</button><a href='".APP_SERVER."/reserva/modificar/telefon/".$id_old."'></a>
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