<?php
global $conn;

$year_old = $params['any'];
$month_old = $params['mes'];
$day_old = $params['dia'];

    switch ($month_old) {
        case '01': $mes3 = "Gener";
                       break;
        case '02': $mes3 = "Febrer";
                       break;
        case '03':  $mes3 = "Març";
                       break;
        case '04':  $mes3 = "Abril";
                        break;
        case '05':  $mes3 = "Maig";
                        break;
        case '06':  $mes3 = "Juny";
                        break;
        case '07':  $mes3 = "Juliol";
                        break;
        case '08':  $mes3 = "Agost";
                        break;
        case '09':  $mes3 = "Setembre";
                        break;
        case '10':  $mes3 = "Octubre";
                        break;
        case '11':  $mes3 = "Novembre";
                        break;
        case '12':  $mes3 = "Desembre";
                        break;
    }

$anyActual = date("Y");

    echo "<div class='container'>
    <h2>Calendari d'entrades:<strong> Dia ".$day_old." // ".$mes3." // ".$year_old ."</strong></h2>";

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
    rc1.id,
    c.nombre,
    c.telefono
    FROM reserves_parking AS rc1
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    LEFT JOIN usuaris AS c ON rc1.idClient = c.id
    WHERE YEAR(rc1.diaEntrada) = $year_old AND MONTH(rc1.diaEntrada) = $month_old AND DAY(rc1.diaEntrada) = $day_old
    GROUP BY rc1.idReserva
    ORDER BY rc1.diaEntrada ASC, rc1.horaEntrada ASC";

    $pdo_statement = $conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    if (!empty($result)) {
        ?>
        <div class="container-lg">
        <div class='table-responsive'>
        <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
            <th>Núm. comanda</th>
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
           $id = $row['id'];
           $nom = $row['nombre'];
           $telefon = $row['telefono'];

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
                echo " ".$nom." // ".$telefon." ";
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
               echo "Afegir";
           } else {
               echo $vuelo1;
           }
           echo "</td>";
           echo "<td>".$limpieza2."</td>";
           echo "<td>";
           if ($checkIn == 1) {
               echo "Check-Out";    
           } elseif ($checkIn == 5) {
            echo "Check-In";  
           } else {
            echo "Reserva completada";
           }
           echo "</td>";
           echo "<td>";
           if (empty($idReserva)) {
               echo "Crear notes";    
           } elseif ( !empty($idReserva) && empty($notes) ) {
               echo "Crear notes";
           } elseif (!empty($notes) ) {
               echo $notes;
           }

           echo "</td>";
           echo "<td>Alta en buscador</td>";
           echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
    }

    echo "</div>";
    require_once(APP_ROOT . '/public/inc/footer.php');
    ?>