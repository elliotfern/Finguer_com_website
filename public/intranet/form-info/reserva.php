<?php

if (isset($_GET['idReserva'])) {
    $idReserva_old = filter_input(INPUT_GET, 'idReserva', FILTER_SANITIZE_NUMBER_INT);
    
    if ( filter_var($idReserva_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

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
        rc1.limpieza
        FROM reserves_parking AS rc1
        left join reservas_buscadores AS b ON rc1.buscadores = b.id
        WHERE rc1.idReserva=$idReserva_old";

        $pdo_statement = $pdo_conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $notes_old = $row['notes'];
        }
            echo "<h2>Informació reserva núm: ".$idReserva_old." </h2>";
            ?>
            <div class='table-responsive'>
<table class='table table-striped'>
<thead class="table-dark">
    <tr>
                <th>Reserva</th>
                <th>Tipo</th>
                <th>Cliente // tel.</th>
                <th>Entrada</th>
                <th>Salida</th>
                <th>Vehículo</th>
                <th>Vuelo regreso</th>
                <th>Limpieza</th>
                <th>Acciones</th>
                <th>Notas</th>
                <th>Buscadores</th>
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
            $dataEntrada4 = date("d-m-Y", strtotime($dataEntrada));
        
            $dataSortida = $row['dataSortida'];
            $dataSortida4 = date("d-m-Y", strtotime($dataSortida));
    
            $tipo = $row['tipo'];
            if ($tipo == 1) {
                $tipoReserva2 = "Finguer Class";
            } elseif ($tipo == 2) {
                 $tipoReserva2 = "Gold Finguer Class";
            }
            $limpieza = $row['limpieza'];
            $string0 = "Tu reserva";
            $string1 = "Tu reserva,Servicio de limpieza exterior";
            $string2 = "Tu reserva,Servicio de lavado exterior + aspirado tapicería interior";
            $string3 = "Tu reserva,Limpieza PRO";
            if (strpos($string0, $limpieza) !== false) {
                $limpieza2 = 0;
            } elseif (strpos($string1, $limpieza) !== false) {
                 $limpieza2 = 1;
            } elseif (strpos($string2, $limpieza) !== false) {
                 $limpieza2 = 2;
            } elseif (strpos($string3, $limpieza) !== false) {
                 $limpieza2 = 3;
            } else {
                $limpieza2 = $limpieza;
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

           echo "<tr>";
           echo "<td>".$idReserva."</td>";
           echo "<td>".$tipoReserva2."</td>";
           echo "<td>".$clientNom." ".$clientCognom." // ".$telefono."</td>";
           echo "<td>".$dataEntrada4." // ".$horaEntrada2."</td>";
           echo "<td>".$dataSortida4." // ".$horaSortida2."</td>";
           echo "<td>".$modelo1." // <a href='canvi-matricula.php?&idReserva=".$idReserva."'>".$matricula1."</a></td>";
           echo "<td>";
           if (empty($vuelo1)) {
               echo "<a href='afegir-vol.php?&idReserva=".$idReserva."' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Añadir</a>";
           } else {
               echo "<a href='canvi-vol.php?&idReserva=".$idReserva."'>".$vuelo1."</a>";
           }
           echo "</td>";
           echo "<td>".$limpieza2."</td>";
           echo "<td>";
           if ($checkIn == 5) {
            echo "<a href='fer-checkin.php?&idReserva=".$idReserva."' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Fer Check-In</a>";    
            } elseif ($checkIn == 1) {
               echo "<a href='fer-checkout.php?&idReserva=".$idReserva."' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Fer Check-Out</a>";    
           } elseif ($checkOut == 2) {
            echo "Reserva completada";  
        }
           echo "</td>";
           echo "<td>";
           if (empty($idReserva)) {
               echo "<a href='afegir-nota.php?&idReserva=".$idReserva."' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear notas</a>";    
           } elseif ( !empty($idReserva) && empty($notes) ) {
               echo "<a href='afegir-nota.php?&idReserva=".$idReserva."' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear notas</a>";
           } elseif (!empty($notes) ) {
               echo "<a href='veure-nota.php?&idReserva=".$idReserva."' class='btn btn-danger btn-sm' role='button' aria-pressed='true'>Ver notas</a>";
           }

           echo "</td>";
           echo "<td>";
           if (empty($idReserva)) {
               echo "<a href='afegir-buscador.php?&idReserva=".$idReserva."' class='btn btn-warning btn-sm' role='button' aria-pressed='true'>Alta en buscador</a>";    
           } elseif ( !empty($idReserva) && empty($buscadores) ) {
               echo "<a href='afegir-buscador.php?&idReserva=".$idReserva."' class='btn btn-warning btn-sm' role='button' aria-pressed='true'>Alta en buscador</a>";
           } elseif (!empty($buscadores ) ) {
               echo "".$nombre." <a href='modificar-buscador.php?&idReserva=".$idReserva."'>(deshacer)</a>";
           }
           echo "</td>";
           echo "</tr>";
           }
           echo "</tbody>";
           echo "</table>";
           echo "</div>";
	}
        
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

require_once('inc/footer.php');
?>

