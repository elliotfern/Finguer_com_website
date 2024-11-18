<?php
global $conn;

$id = $params['id'];

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 1;
       
        echo "<div class='container'>";
        echo "<h3>Eliminar reserva del sistema</h3>";

        // consulta general reserves 
        $sql = "SELECT r.id, r.idReserva
        FROM reserves_parking AS r
        WHERE r.id=$id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva = $row['idReserva'];
        }
        
        echo "<h4>ID Reserva: ".$idReserva." </h4>";

        if (isset($_POST["remove-reserva"])) {
                            $emailSent = true;

                            $sql = "DELETE FROM reserves_parking
                            WHERE id=:id";
                            $stmt = $conn->prepare($sql);
                            $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $codi_resposta = 3;
            } else {
                $codi_resposta = 2;
            }
        }
                        
            if ($codi_resposta == 3)  {
                            echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Eliminació realizada correctament.</strong></h4>';
                            echo "Eliminació de la reserva amb èxit.</div>";
            } elseif ($codi_resposta == 2)  {
                                echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                                echo 'Les dades no s\'han transmès correctament.</div>';
            }
                    
            if ($codi_resposta == 1) { 
                            echo '<form action="" method="post" id="remove-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';
                    
                            echo "<hr>";
                            echo "<h4>Estàs segur que vols eliminar aquesta reserva?</h4>";
                            echo '<form method="post" action="">';

                            echo "<div class='md-12'>";
                            echo "<button id='remove-reserva' name='remove-reserva' type='submit' class='btn btn-primary'>Eliminar reserva</button><a href='".APP_SERVER."/reserva/eliminar/reserva/".$id_old."'></a>
                            </div>";

                            echo "</form>";
                
            } else {
                echo '<a href="'.APP_WEB.'/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
            }
        
                        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
        
}

echo "</div>";
echo '</div>';
echo "</div>";

require_once(APP_ROOT . '/public/inc/footer.php');
?>
