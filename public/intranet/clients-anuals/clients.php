<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container' style='margin-bottom:100px'>";
echo "<h3>Clients amb Abonament anual</h3>";

// consulta general clients
$sql = "SELECT c.nombre AS nom, c.telefono AS telefon, c.uuid, c.anualitat
    FROM usuarios AS c
    WHERE c.tipo_rol = 'cliente_anual'
    ORDER BY c.nombre ASC";

$pdo_statement = $conn->prepare($sql);
$pdo_statement->execute();
$result = $pdo_statement->fetchAll();

?>
<div class='table-responsive'>
    <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
                <th>Nom i cognoms &darr;</th>
                <th>Telèfon</th>
                <th>Anualitat</th>
                <th>Modificar dades</th>
                <th>Eliminar client</th>
                <th>Crear reserva</th>
            </tr>
        </thead>
        <tbody>

            <?php

            foreach ($result as $row) {
                $nom = $row['nom'];
                $telefon = $row['telefon'];
                $id = $row['uuid'];
                $anualitat = $row['anualitat'];
                echo "<tr>";
                echo "<td>" . $nom . "</td>";
                echo "<td>" . $telefon . "</td>";
                echo "<td>" . $anualitat . "</td>";
                echo "<td><a href='" . APP_WEB . "/control/clients-anuals/modificar/client/" . $id . "' class='btn btn-warning btn-sm' role='button' aria-pressed='true'>Actualitzar dades</a></td>";
                echo "<td><a href='" . APP_WEB . "/control/clients-anuals/eliminar/client/" . $id . "' class='btn btn-danger btn-sm' role='button' aria-pressed='true'>Eliminar client</a></td>";
                echo "<td><a href='" . APP_WEB . "/control/clients-anuals/crear-reserva/" . $id . "' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear reserva</a></td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
 ?>
            </div>

           