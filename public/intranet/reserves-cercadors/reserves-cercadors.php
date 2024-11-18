<?php
require_once('inc/header.php');

echo "<h2>Reserves cercadors</h2>";

$sql = "SELECT rc1.idReserva,
rc1.buscadores,
b.nombre
FROM reserves_parking AS rc1
inner join reservas_buscadores AS b ON rc1.buscadores = b.id
GROUP BY rc1.buscadores
ORDER BY rc1.buscadores ASC";

    $pdo_statement = $pdo_conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    if (!empty($result)) {

        ?>
        <div class="container-lg">
        <div class='table-responsive'>
        <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
                <th>Cercador &darr;</th>
                </tr>
                </thead>
            <tbody>

	    <?php
        foreach($result as $row) {
            $idBuscador = $row['buscadores'];
            $nombre = $row['nombre'];
            echo "<tr>";
            echo "<td><a href='reserves-cercadors-info.php?&cercador=".$idBuscador."'>".$nombre."</a></td>";
            echo "</tr>";
        }
            echo "</tbody>";
            echo "</table>";
            echo "</div>";
    }

require_once('inc/footer.php');
?>