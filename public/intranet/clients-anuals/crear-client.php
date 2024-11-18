<?php
global $conn;
require_once(APP_ROOT . '/public/inc/header-reserves-anuals.php');

echo "<div class='container'>";
echo "<h3>Alta nou client Abonament anual</h3>";

$codi_resposta = 2;
	
              if (isset($_POST["alta-client"])) {
                  
                if (empty($_POST["nombre"])) {
                    $hasError = true;
                } else {
                    $nombre = data_input($_POST["nombre"], ENT_NOQUOTES);
                }
                
                if (empty($_POST["telefono"])) {
                    $telefono = NULL;
                } else {
                    $telefono = data_input($_POST["telefono"], ENT_NOQUOTES);
                }
                
                if (empty($_POST["anualitat"])) {
                    $anualitat = NULL;
                } else {
                    $anualitat = data_input($_POST["anualitat"], ENT_NOQUOTES);
                }
                
                $tipoUsuario = 3;

               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 

                    $sql = "INSERT INTO usuaris SET nombre=:nombre, telefono=:telefono, anualitat=:anualitat, tipoUsuario=:tipoUsuario";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":nombre", $nombre, PDO::PARAM_STR);
                    $stmt->bindParam(":telefono", $telefono, PDO::PARAM_STR);
                    $stmt->bindParam(":anualitat", $anualitat, PDO::PARAM_STR);
                    $stmt->bindParam(":tipoUsuario", $tipoUsuario, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
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
                    echo '<form action="" method="post" id="alta-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';
            
                    echo '<div class="col-md-4">';
                    echo '<label>Nom i cognoms client:</label>';
                    echo '<input type="text" class="form-control" id="nombre" name="nombre">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Telèfon client:</label>';
                    echo '<input type="text" class="form-control" id="telefono" name="telefono">';
                    echo '</div>';

                    echo '<div class="col-md-6">';
                    echo '<label>Anualitat client:</label>';
                    echo '<input type="text" class="form-control" id="anualitat" name="anualitat">';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='alta-client' name='alta-client' type='submit' class='btn btn-primary'>Alta client</button><a href='".APP_WEB."/clients-anuals/crear/client/'></a>
                    </div>";
        
                    echo "</form>";
                } else {
                    echo '<a href="'.APP_WEB.'/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
                }

                echo '</div>
                </div>';

echo "</div>";
require_once(APP_ROOT . '/public/inc/footer.php');
?>
