<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";
?>

<h2>Estat 2: Reserves clients anuals al parking</h2>
<h4>Ordenat segons data sortida vehicle</h4>

<?php
// consulta general reserves 
$pdo_statement = $conn->prepare("SELECT rc1.idReserva,
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
    rc1.limpieza,
    rc1.id,
    c.nombre,
    c.telefono
    FROM reserves_parking AS rc1
    LEFT JOIN usuaris AS c ON rc1.idClient = c.id
    WHERE rc1.checkIn = 1 AND rc1.idReserva = 1 AND c.tipo_rol = 'cliente_anual'
    GROUP BY rc1.id
    ORDER BY rc1.diaSalida ASC, rc1.horaSalida  ASC");
$pdo_statement->execute();
$result = $pdo_statement->fetchAll();
if (!empty($result)) {
?>
    <div class="container">
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
                        <th>Modifica reserva</th>
                    </tr>
                </thead>
                <tbody>

                    <?php

                    foreach ($result as $row) {
                        $matricula1 = $row['matricula'];
                        $modelo1 = $row['modelo'];
                        $vuelo1 = $row['vuelo'];

                        $horaEntrada2 = $row['HoraEntrada'];
                        $horaSortida2 = $row['HoraSortida'];

                        $dataEntrada = $row['dataEntrada'];
                        $anyEntrada = date('Y', strtotime($dataEntrada));
                        $dataEntrada4 = date("d-m-Y", strtotime($dataEntrada));

                        $dataSortida = $row['dataSortida'];
                        $anySortida = date('Y', strtotime($dataSortida));
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
                        $nom = $row['nombre'];
                        $telefon = $row['telefono'];
                        $id = $row['id'];
                        echo "<tr>";
                        echo "<td>";
                        if ($idReserva == 1) {
                            echo "<button type='button' class='btn btn-primary btn-sm'>Client anual</button>";
                        } else {
                            echo "" . $idReserva . "</a>";
                        }

                        echo "<td>" . $tipoReserva2 . "</td>";
                        echo "<td>";
                        if ($idReserva == 1) {
                            echo " " . $nom . " // " . $telefon . " ";
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($anyEntrada == 1970) {
                            echo "Pendent";
                        } else {
                            echo "" . $dataEntrada4 . " // " . $horaEntrada2 . "";
                        }
                        echo "</td>";
                        echo "<td>";
                        if ($anySortida == 1970) {
                            echo "Pendent";
                        } else {
                            echo "" . $dataSortida4 . " // " . $horaSortida2 . "";
                        }
                        echo "</td>";
                        echo "<td>" . $modelo1 . " // <a href='" . APP_WEB . "/reserva/modificar/vehicle/" . $id . "'>" . $matricula1 . "</a></td>";
                        echo "<td>";
                        if (empty($vuelo1)) {
                            echo "<a href='" . APP_WEB . "/reserva/modificar/vol/" . $id . "'>Afegir vol</a>";
                        } else {
                            echo "<a href='" . APP_WEB . "/reserva/modificar/vol/" . $id . "'>" . $vuelo1 . "</a>";
                        }
                        echo "</td>";
                        echo "<td>" . $limpieza2 . "</td>";
                        echo "<td>";
                        if ($checkIn == 1) {
                            echo "<a href='" . APP_WEB . "/reserva/fer/check-out/" . $id . "' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Check-Out</a>";
                        }
                        echo "</td>";
                        echo "<td>";
                        if (empty($idReserva)) {
                            echo "<a href='" . APP_WEB . "/reserva/modificar/nota/" . $id . "' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear notes</a>";
                        } elseif (!empty($idReserva) && empty($notes)) {
                            echo "<a href='" . APP_WEB . "/reserva/modificar/nota/" . $id . "' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear notes</a>";
                        } elseif (!empty($notes)) {
                            echo "<a href='" . APP_WEB . "/reserva/modificar/nota/" . $id . "' class='btn btn-danger btn-sm' role='button' aria-pressed='true'>Veure notes</a>";
                        }

                        echo "</td>";
                        echo "<td>";
                        echo "<a href='" . APP_WEB . "/reserva/modificar/reserva/" . $id . "' class='btn btn-dark btn-sm' role='button' aria-pressed='true'>Modificar reserva</a>";
                        echo "</td>";
                        echo "</tr>";
                    }
                    echo "</tbody>";
                    echo "</table>";
                    echo "</div>";



                    ?>
                <?php
                $sql2 = "SELECT COUNT(r.idReserva) AS numero
        FROM reserves_parking as r
        WHERE r.checkIn = 1 AND r.idReserva = 1";

                $pdo_statement = $conn->prepare($sql2);
                $pdo_statement->execute();
                $result = $pdo_statement->fetchAll();
                foreach ($result as $row) {
                    $numero = $row['numero'];
                }

                echo "<h5>Total reserves actualment al parking: " . $numero . " </h5>";
                echo "</div>";
            } else {
                echo "En aquests moments no hi ha cap reserva de client anual al parking";
            }

            echo "</div>";

                ?>