<?php
require_once('inc/header.php');

if (isset($_GET['cercador'])) {
    $buscador = $_GET['cercador'];
}

    $sql = "SELECT b.nombre
    FROM reservas_buscadores AS b
    WHERE b.id = $buscador";

    $pdo_statement = $pdo_conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    foreach($result as $row) {
        $nombre = $row['nombre'];
    }

echo "<h2>Reserves cercadors</h2>";
echo "<h3>Cercador: ".$nombre."</h3>";

    $sql2 = "SELECT rc1.idReserva,
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
    WHERE rc1.buscadores=$buscador
    GROUP BY rc1.idReserva
    ORDER BY rc1.diaEntrada DESC, rc1.horaEntrada DESC";

    /* Pagination Code starts */
	$per_page_html = '';
	$page = 1;
	$start=0;
	if(!empty($_POST["page"])) {
		$page = $_POST["page"];
		$start=($page-1) * ROW_PER_PAGE;
	}
	$limit=" limit " . $start . "," . ROW_PER_PAGE;
	$pagination_statement = $pdo_conn->prepare($sql2);
	$pagination_statement->execute();

	$row_count = $pagination_statement->rowCount();
	if(!empty($row_count)){
		$per_page_html .= "<div style='text-align:center;margin:20px 0px;'>";
		$page_count=ceil($row_count/ROW_PER_PAGE);
		if($page_count>1) {
			for($i=1;$i<=$page_count;$i++){
				if($i==$page){
					$per_page_html .= '<input type="submit" name="page" value="' . $i . '" class="btn-page current" />';
				} else {
					$per_page_html .= '<input type="submit" name="page" value="' . $i . '" class="btn-page" />';
				}
			}
		}
		$per_page_html .= "</div>";
	}
	
	$query = $sql2.$limit;
    $pdo_statement = $pdo_conn->prepare($query);
    $pdo_statement->execute();
    $result2 = $pdo_statement->fetchAll();
    if (!empty($result2)) {

        ?>
        <form name='frmSearch' action='' method='post'>
        <div class="container-lg">
        <div class='table-responsive'>
        <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
            <th>Reserva</th>
                <th>Tipo</th>
                <th>Cliente // tel.</th>
                <th>Entrada &darr;</th>
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
        foreach($result2 as $row) {
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

echo "".$per_page_html."";
echo "</form>";

require_once('inc/footer.php');
?>