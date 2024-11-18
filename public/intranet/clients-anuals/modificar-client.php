<?php
$idClient = $params['idClient'];

global $conn;
require_once(APP_ROOT . '/public/inc/header-reserves-anuals.php');

echo "<div class='container'>";
echo "<h3>Modificar dades client Abonament anual</h3>";

if (is_numeric($idClient)) {
    $idClient_old = intval($idClient);
    
    if ( filter_var($idClient_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT c.nombre AS nom, c.telefono AS telefon, c.id, c.anualitat
        FROM usuaris AS c
        WHERE c.id=$idClient_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $nom_old = $row['nom'];
            $telefon_old = $row['telefon'];
            $anualitat_old = $row['anualitat'];
        }
    echo "<h4>Client: ".$nom_old." </h4>";
    
$codi_resposta = 2;

              if (isset($_POST["update-client"])) {
                global $pdo_conn;
                  
                if (empty($_POST["nombre"])) {
                    $hasError = true;
                } else {
                    $nombre = data_input($_POST["nombre"]);
                }
                

                if (empty($_POST["telefono"])) {
                    $telefono = data_input($_POST["telefono"]);
                } else {
                    $telefono = data_input($_POST["telefono"]);
                }

                if (empty($_POST["anualitat"])) {
                    $anualitat = NULL;
                } else {
                    $anualitat = data_input($_POST["anualitat"]);
                }

               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;

                    $sql = "UPDATE usuaris SET nombre=:nombre, telefono=:telefono, anualitat=:anualitat
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
                    $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
                    $stmt->bindParam(":anualitat", $anualitat, PDO::PARAM_STR);
                    $stmt->bindParam(":id", $idClient_old, PDO::PARAM_INT);

                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 

                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Alta realizada correctament.</strong></h4>';
                    echo "Alta client anual amb èxit.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';
            
                    echo '<div class="col-md-4">';
                    echo '<label>Nom i cognoms client:</label>';
                    echo '<input type="text" class="form-control" id="nombre" name="nombre" value="'.$nom_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Telèfon client:</label>';
                    echo '<input type="text" class="form-control" id="telefono" name="telefono" value="'.$telefon_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-6">';
                    echo '<label>Anualitat client:</label>';
                    echo '<input type="text" class="form-control" id="anualitat" name="anualitat" value="'.$anualitat_old.'">';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='update-client' name='update-client' type='submit' class='btn btn-primary'>Modifica client</button><a href='".APP_WEB."/clients-anuals/modificar/client/".$idClient_old."'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }

                
            } else {
                echo "Error: aquest ID no és vàlid";
            }

} else {
   echo "Error. No has seleccionat cap client.";
}

echo '</div>';
echo "</div>";

require_once(APP_ROOT . '/public/inc/footer.php');
?>