<?php
global $conn;

$exit = false;
?>

<div class="container">
<h2>Cercador de reserves</h2>
<h4>Cerca per número de reserva</h4>

<?php

function seo_friendly_url($string){
    $string = str_replace(array('[\', \']'), '', $string);
    $string = preg_replace('/\[.*\]/U', '', $string);
    $string = preg_replace('/&(amp;)?#?[a-z0-9]+;/i', '-', $string);
    $string = htmlentities($string, ENT_COMPAT, 'utf-8');
    $string = preg_replace('/&([a-z])(acute|uml|circ|grave|ring|cedil|slash|tilde|caron|lig|quot|rsquo);/i', '\\1', $string );
    $string = preg_replace(array('/[^a-z0-9]/i', '/[-]+/') , '-', $string);
    return strtolower(trim($string, '-'));
}



if (!empty($_REQUEST['term2'])) {

    $term_net2 = seo_friendly_url($_REQUEST['term2']);
      if (!is_string($term_net2)) {
        $exit = false;
        $errorMessage = "<h6>Error.</h6>";
      } else {
        $exit = true;
      }
    }


echo '<form action="" method="POST" id="submit2" class="row g-3">';

echo '<div class="col-md-12">';
echo '<input type="text" name="term2" placeholder="Núm reserva" >';
echo "</div>";

echo '<div class="col-12">';
echo '<input type="submit" class="btn btn-primary" name="submit2" value="Cerca">';
echo "</div>";

echo '</form>';

if ($exit === true) {
    // consulta general reserves 
    $sql = "SELECT rc1.idReserva,
    rc1.firstName AS 'clientNom',
    rc1.lastName AS 'clientCognom',
    rc1.tel AS 'telefono',
    rc1.diaSalida AS 'dataSortida',
    rc1.horaEntrada AS 'HoraEntrada',
    rc1.horaSalida AS 'HoraSortida',
    rc1.diaEntrada AS 'dataEntrada',
    rc1.matricula AS 'matricula',
    rc1.vehiculo AS 'modelo',
    rc1.vuelo AS 'vuelo',
    rc1.tipo AS 'tipo',
    rc1.checkIn,
    rc1.checkOut,
    rc1.notes,
    rc1.buscadores,
    rc1.limpieza,
    c.nombre,
    c.telefono,
    rc1.id
    FROM reserves_parking AS rc1
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    LEFT JOIN usuaris AS c ON rc1.idClient = c.id
    WHERE rc1.idReserva='".$term_net2."'
    GROUP BY rc1.id
    ORDER BY rc1.diaSalida ASC, rc1.horaSalida  ASC";

    $pdo_statement = $conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    foreach($result as $row) {
        $id_old = $row['idReserva'];
        $notes_old = $row['notes'];
    }
    echo "<br><br>";    
    echo "<h2>Reserva número: ".$term_net2." </h2>";
        ?>
        <div class='table-responsive'>
<table class='table table-striped'>
<thead class="table-dark">
<tr>
                <th>Reserva</th>
                <th>Tipus</th>
                <th>Client // tel.</th>
                <th>Entrada &darr;</th>
                <th>Sortida</th>
                <th>Vehicle</th>
                <th>Vol tornada</th>
                <th>Neteja</th>
                <th>Accions</th>
                <th>Notes</th>
                <th>Cercadors</th>
            </tr>
            </thead>
            <tbody>

<?php
if(!empty($result)) { 
    foreach($result as $row) {

        $matricula1 = $row['matricula'];
            $modelo1 = $row['modelo'];
            $vuelo1 = $row['vuelo'];

            $horaEntrada2 = $row['HoraEntrada'];
            $horaSortida2 = $row['HoraSortida'];
    
            $dataEntrada = $row['dataEntrada'];
            $anyEntrada = date('Y', strtotime( $dataEntrada));
            $dataEntrada4 = date("d-m-Y", strtotime($dataEntrada));
        
            $dataSortida = $row['dataSortida'];
            $anySortida = date('Y', strtotime( $dataSortida));
            $dataSortida4 = date("d-m-Y", strtotime($dataSortida));
    
            $tipo = $row['tipo'];
            if ($tipo == 1) {
                $tipoReserva2 = "Finguer Class";
            } elseif ($tipo == 2) {
                 $tipoReserva2 = "Gold Finguer Class";
            } else {
                $tipoReserva2 = "Finguer Class";
            }
            $limpieza = $row['limpieza'];
            if ($limpieza == 1) {
                $limpieza2 = "Servicio de limpieza exterior";
            } elseif ($limpieza == 2) {
                 $limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
            } elseif ($limpieza == 3) {
                $limpieza2 = "Limpieza PRO";
            } else {
                $limpieza2 = "-";
            }

           $idReserva = $row['idReserva'];
           $checkIn = $row['checkIn'];
           $checkOut = $row['checkOut'];
           $notes = $row['notes'];
           $buscadores = $row['buscadores'];
            if ($buscadores == 1) {
                $buscadores = "One park";
            } elseif ($buscadores == 2) {
                $buscadores = "Parkcloud";
            } elseif ($buscadores == 3) {
                $buscadores = "Travelcar";
            } elseif ($buscadores == 4) {
                $buscadores = "Looking 4 parking";
            } elseif ($buscadores == 5) {
                $buscadores = "icarous";
            }

           $clientNom = $row['clientNom'];
           $clientCognom = $row['clientCognom'];
           $telefono = $row['telefono'];
           $nom = $row['nombre'];
           $telefon = $row['telefono'];
           $id = $row['id'];

           echo "<tr>";
           echo "<td>";
            if ($idReserva == 1) {
                echo "<button type='button' class='btn btn-primary btn-sm'>Client anual</button>";
            } else {
                echo "".$idReserva."</a>";
            }
        
           echo "</td>";
           echo "<td>".$tipoReserva2."</td>";
           echo "<td>";
            if ($idReserva == 1) {
                echo " ".$nom." // ".$telefon." ";
            } else {
                if ($nom == "") {
                    echo "".$clientNom." ".$clientCognom." // ".$telefono."";
                } else {
                    echo " ".$nom." // ".$telefon." ";
                } 
            } 
            echo "</td>";
            echo "<td>";
                   if ($anyEntrada == 1970) {
                    echo "Pendent";
                   } else {
                    echo "".$dataEntrada4." // ".$horaEntrada2."";
                   }
                   echo "</td>";
                   echo "<td>";
                   if ($anySortida == 1970) {
                    echo "Pendent";
                   } else {
                    echo "".$dataSortida4." // ".$horaSortida2."";
                   }
            echo "</td>";
           echo "<td>".$modelo1." ";
            if (!empty($matricula1)) {
                echo $matricula1;
            } else {
                echo "<p>Afegir matrícula</p>";
            }
           echo "</td>";

           echo "<td>";
           if (empty($vuelo1)) {
               echo "Afegir vol";
           } else {
               echo $vuelo1;
           }
           echo "</td>";
           echo "<td>".$limpieza2."</td>";
           echo "<td>";
            if ($checkIn == 5) {
               echo "<a href='".APP_WEB."/reserva/fer/check-in/".$id."' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Check-In</a>";    
            } elseif ($checkIn == 6) {
            echo "Reserva cancel·lada";    
            } elseif ($checkIn == 1) {
                echo "<a href='".APP_WEB."/reserva/fer/check-in/".$id."' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Check-Out</a>";    
            } elseif ($checkOut == 2) {
                echo "Reserva completada";    
            }
           echo "</td>";
           echo "<td>";
            echo $notes;
           echo "</td>";
           echo "<td>Alta en buscador</td>";
           echo "</tr>";
       }
       echo "</tbody>";
       echo "</table>";
       echo "</div>";
  

}

}

?>
</div>

<?php 
echo "</div>";
require_once(APP_ROOT . '/public/inc/footer.php');
?>