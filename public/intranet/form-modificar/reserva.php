<?php
global $conn;

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);
   
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.idClient, r.importe, r.processed, r.fechaReserva, r.tipo, u.nombre, r.horaEntrada, r.diaEntrada, r.horaSalida, r.diaSalida, r.vehiculo, r.matricula, r.vuelo, r.limpieza, r.notes, r.buscadores
        FROM reserves_parking AS r
        LEFT JOIN usuaris AS u ON r.idClient = u.id
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva_old = $row['idReserva'];
            $idClient_old = $row['idClient'];
            $importe_old = $row['importe'];
            $processed_old = $row['processed'];
            $fechaReserva_old = $row['fechaReserva'];
            $tipo_old = $row['tipo'];
            $nombre_old = $row['nombre'];
            $horaEntrada_old = $row['horaEntrada'];
            $diaEntrada_old = $row['diaEntrada'];
            $horaSalida_old = $row['horaSalida'];
            $diaSalida_old = $row['diaSalida'];
            $vehiculo_old = $row['vehiculo'];
            $matricula_old = $row['matricula'];
            $vuelo_old = $row['vuelo'];
            $limpieza_old = $row['limpieza'];

            if ($limpieza_old == 1) {
                $limpieza2 = "Servicio de limpieza exterior";
            } elseif ($limpieza_old == 2) {
                 $limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
            } elseif ($limpieza_old == 3) {
                $limpieza2 = "Limpieza PRO";
            } else {
                $limpieza2 = "-";
            }

            $notes_old = $row['notes'];
            $buscadores_old = $row['buscadores'];
        }
        
        if ($fechaReserva_old !== NULL) {
            $fecha_formateada = "<h4>Reserva efectuada el dia: " . date('d-m-Y H:i:s', strtotime($fechaReserva_old)) . "</h4>";
        } else {
            $fecha_formateada = "";
        }
        
        echo "<div class='container'>
        <h2>Modificació ID Reserva: ".$idReserva_old." </h2>";
        echo $fecha_formateada;

              if (isset($_POST["update"])) {                  
                if (empty($_POST["buscadores"])) {
                    $buscadores = data_input($_POST["buscadores"]);
                } else {
                    $buscadores = data_input($_POST["buscadores"]);
                }

                if (empty($_POST["importe"])) {
                    $importe = data_input($_POST["importe"]);
                } else {
                    $importe = data_input($_POST["importe"]);
                }

                if (empty($_POST["processed"])) {
                    $processed = data_input($_POST["processed"]);
                } else {
                    $processed = data_input($_POST["processed"]);
                }

                if (empty($_POST["tipo"])) {
                    $tipo = data_input($_POST["tipo"]);
                } else {
                    $tipo = data_input($_POST["tipo"]);
                }

                if (empty($_POST["horaEntrada"])) {
                    $horaEntrada = data_input($_POST["horaEntrada"]);
                } else {
                    $horaEntrada = data_input($_POST["horaEntrada"]);
                }

                if (empty($_POST["diaEntrada"])) {
                    $diaEntrada = data_input($_POST["diaEntrada"]);
                } else {
                    $diaEntrada = data_input($_POST["diaEntrada"]);
                }

                if (empty($_POST["horaSalida"])) {
                    $horaSalida = data_input($_POST["horaSalida"]);
                } else {
                    $horaSalida = data_input($_POST["horaSalida"]);
                }
               
                if (empty($_POST["diaSalida"])) {
                    $diaSalida = data_input($_POST["diaSalida"]);
                } else {
                    $diaSalida = data_input($_POST["diaSalida"]);
                }

                if (empty($_POST["vehiculo"])) {
                    $vehiculo = data_input($_POST["vehiculo"]);
                } else {
                    $vehiculo = data_input($_POST["vehiculo"]);
                }

                if (empty($_POST["matricula"])) {
                    $matricula = data_input($_POST["matricula"]);
                } else {
                    $matricula = data_input($_POST["matricula"]);
                }

                if (empty($_POST["vuelo"])) {
                    $vuelo = data_input($_POST["vuelo"]);
                } else {
                    $vuelo = data_input($_POST["vuelo"]);
                }

                if (empty($_POST["notes"])) {
                    $notes = data_input($_POST["notes"]);
                } else {
                    $notes = data_input($_POST["notes"]);
                }
                  
               // Si no hi ha cap error, envia el formulari
                if (!isset($hasError)) {
                    $emailSent = true;
          
                } else { // Error > bloqueja i mostra avis
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                    echo 'Controla que totes les dades siguin correctes.</div>';
                } 
          
                    $sql = "UPDATE reserves_parking SET buscadores=:buscadores, importe=:importe, processed=:processed, tipo=:tipo, horaEntrada=:horaEntrada, diaEntrada=:diaEntrada, horaSalida=:horaSalida, diaSalida=:diaSalida, vehiculo=:vehiculo, matricula=:matricula, vuelo=:vuelo, limpieza=:limpieza, notes=:notes
                    WHERE id=:id";
                    $stmt = $conn->prepare($sql);
                    $stmt->bindParam(":buscadores", $buscadores, PDO::PARAM_INT);
                    $stmt->bindParam(":importe", $importe, PDO::PARAM_INT);
                    $stmt->bindParam(":processed", $processed, PDO::PARAM_INT);
                    $stmt->bindParam(":tipo", $tipo, PDO::PARAM_INT);
                    $stmt->bindParam(":horaEntrada", $horaEntrada, PDO::PARAM_STR);
                    $stmt->bindParam(":diaEntrada", $diaEntrada, PDO::PARAM_STR);
                    $stmt->bindParam(":horaSalida", $horaSalida, PDO::PARAM_STR);
                    $stmt->bindParam(":diaSalida", $diaSalida, PDO::PARAM_STR);
                    $stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
                    $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
                    $stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
                    $stmt->bindParam(":limpieza", $limpieza, PDO::PARAM_INT);
                    $stmt->bindParam(":notes", $notes, PDO::PARAM_STR);

                    $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                    
                    if ($stmt->execute()) {
                        $codi_resposta = 1;
                    } else {
                        $codi_resposta = 2;
                    }
          
                    if ($codi_resposta == 1)  {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Reserva actualizada correctament.</strong></h4>';
                    echo "Reserva actualizada.</div>";
                    } else { // Error > bloqueja i mostra avis
                        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                        echo 'Les dades no s\'han transmès correctament.</div>';
                    }
                }
            
                if ($codi_resposta == 2) { 
                    echo '<form action="" method="post" id="update" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

                    echo '<h4>Dades de la reserva:</h4>';

                    echo '<div class="col-md-4">';
                    echo '<label>Nom i cognoms client:</label>';
                    echo '<input type="text" class="form-control" id="nombre" readonly value="'.$nombre_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Import reserva:</label>';
                    echo '<input type="text" class="form-control" id="importe" name="importe" value="'.$importe_old.'">';
                    echo '</div>';

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

                    echo '<div class="col-md-4">';
                    echo '<label>Reserva pagada:</label>';
                    echo '<select class="form-select" name="processed" id="processed">';
                    echo '<option selected disabled>Selecciona una opció:</option>';
                    
                    if ($processed_old == 1) {
                        echo "<option value='1' selected>SI</option>";
                        echo "<option value='2'>NO</option>"; 
                      } else {
                        echo "<option value='1'>SI</option>";
                        echo "<option value='2' selected>NO</option>";
                      }
                    echo '</select>';
                    echo '</div>';

                    echo "<hr>";
                    echo "<h4>Entrada i sortida:</h4>";

                    echo '<div class="col-md-6">';
                    echo '<label>Dia entrada:</label>';
                    echo '<input type="date" class="form-control" id="diaEntrada" name="diaEntrada" value="'.$diaEntrada_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-6">';
                    echo '<label>Hora entrada:</label>';
                    echo '<input type="text" class="form-control" id="horaEntrada" name="horaEntrada" value="'.$horaEntrada_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-6">';
                    echo '<label>Dia sortida:</label>';
                    echo '<input type="date" class="form-control" id="diaSalida" name="diaSalida" value="'.$diaSalida_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-6">';
                    echo '<label>Hora sortida:</label>';
                    echo '<input type="text" class="form-control" id="horaSalida" name="horaSalida" value="'.$horaSalida_old.'">';
                    echo '</div>';

                    echo "<hr>";

                    echo '<div class="col-md-4">';
                    echo '<label>Model vehicle:</label>';
                    echo '<input type="text" class="form-control" id="vehiculo" name="vehiculo" value="'.$vehiculo_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Número de matrícula:</label>';
                    echo '<input type="text" class="form-control" id="matricula" name="matricula" value="'.$matricula_old.'">';
                    echo '</div>';

                    echo '<div class="col-md-4">';
                    echo '<label>Vol client:</label>';
                    echo '<input type="text" class="form-control" id="vuelo" name="vuelo" value="'.$vuelo_old.'">';
                    echo '</div>';
        

                    echo '<div class="col-md-4">';
                    echo '<label>Nota reserva:</label>';
                    echo '<input type="text" class="form-control" id="notes" name="notes"  value="'.$notes_old.'">';
                    echo '</div>';
            
                    echo '<div class="col-md-4">';
                    echo '<label>Neteja:</label>';
                    echo '<input type="text" class="form-control" id="nombre" readonly value="'.$limpieza2.'">';
                    echo '</div>';
              
                    echo '<div class="col-md-4">';
                    echo '<label>Buscador:</label>';
                    echo '<select class="form-select" name="buscadores" id="buscadores">';
                    echo '<option selected disabled>Selecciona una opció:</option>';
                    echo "<option value='NULL' selected>Sense buscador</option>"; 
                    $sql = "SELECT b.id, b.nombre
                    FROM reservas_buscadores AS b
                    ORDER BY b.nombre ASC";

                    $pdo_statement = $conn->prepare($sql);
                    $pdo_statement->execute();
                    $result = $pdo_statement->fetchAll();
                    foreach($result as $row) {
                        $id = $row['id'];
                        $nombre = $row['nombre'];
                        if ($buscadores_old == $id) {
                            echo "<option value=".$buscadores_old." selected>".$nombre."</option>"; 
                          } else {
                            echo "<option value=".$id.">".$nombre."</option>"; 
                          }
                      }
                    echo '</select>';
                    echo '</div>';
        
                    echo "<div class='md-12'>";
                    echo "<button id='update' name='update' type='submit' class='btn btn-primary'>Modificar reserva</button><a href='".APP_SERVER."/reserva/modificar/reserva/".$id_old."'></a>
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