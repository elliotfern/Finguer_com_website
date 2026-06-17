<?php
global $conn;
require_once APP_ROOT . '/intranet/inc/header.php'; 
require_once(APP_ROOT . '/intranet/inc/header-reserves-anuals.php');

echo "<div class='container' style='margin-bottom:100px'>";
echo "<h3>Clients amb Abonament anual</h3>";

// SQL principal con reservas dentro del periodo de anualidad
$sql = "
SELECT 
    c.nombre AS nom,
    c.telefono AS telefon,
    HEX(c.uuid) AS uuid_hex,
    c.estado,
    a.fecha_inicio,
    a.fecha_fin,
    COALESCE(r.reservas_completadas, 0) AS reservas_completadas
FROM usuarios AS c
LEFT JOIN usuarios_abonos AS a 
    ON c.uuid = a.usuario_uuid

LEFT JOIN (
    SELECT 
        r.usuario_uuid,
        COUNT(*) AS reservas_completadas
    FROM parking_reservas r
    INNER JOIN usuarios_abonos a2 
        ON a2.usuario_uuid = r.usuario_uuid
    WHERE r.estado = 'anual'
      AND r.fecha_reserva BETWEEN a2.fecha_inicio AND a2.fecha_fin
    GROUP BY r.usuario_uuid
) r 
    ON r.usuario_uuid = c.uuid

WHERE c.tipo_rol = 'cliente_anual'
  AND c.estado <> 'eliminado'
ORDER BY c.nombre ASC;
";

$pdo_statement = $conn->prepare($sql);
$pdo_statement->execute();
$result = $pdo_statement->fetchAll();
?>

<div class='table-responsive'>
    <table class='table table-striped'>
        <thead class="table-dark">
            <tr>
                <th>Nom i cognoms ↓</th>
                <th>Telèfon</th>
                <th>Fi Anualitat</th>
                <th>Reserves completades</th>
                <th>Estat</th>
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
            $id = $row['uuid_hex'];
            $fecha_fin = $row['fecha_fin'];
            $estado = htmlspecialchars($row['estado'], ENT_QUOTES);
            $reservas_completadas = $row['reservas_completadas'];

            // ---- ALERTA 30 DÍAS ----
            $hoy = new DateTime();
            $caducaPronto = false;

            if ($fecha_fin) {
                $fin = new DateTime($fecha_fin);
                $diffDias = $hoy->diff($fin)->days;

                $caducaPronto = ($fin > $hoy && $diffDias <= 30);
            }

            $rowClass = $caducaPronto ? "table-danger" : "";

            echo "<tr class='{$rowClass}'>";

            echo "<td>{$nom}</td>";
            echo "<td>{$telefon}</td>";
            echo "<td>{$fecha_fin}</td>";
            echo "<td><strong>{$reservas_completadas}</strong></td>";
            echo "<td><span class='badge bg-secondary'>{$estado}</span></td>";

            if (auth_is_admin()) {

                echo "<td>
                    <a href='" . APP_WEB . "/control/clients-anuals/modifica-client/{$id}'
                       class='btn btn-warning btn-sm'>
                       Actualitzar dades
                    </a>
                </td>";

                echo "<td>
                    <a href='" . APP_WEB . "/control/clients-anuals/eliminar-client/{$id}'
                       class='btn btn-danger btn-sm'>
                       Eliminar client
                    </a>
                </td>";

            } else {
                echo "<td class='text-muted text-center'>–</td>";
                echo "<td class='text-muted text-center'>–</td>";
            }

            echo "<td>
                <a href='" . APP_WEB . "/control/clients-anuals/crear-reserva/{$id}'
                   class='btn btn-info btn-sm'>
                   Crear reserva
                </a>
            </td>";

            echo "</tr>";
        }
        ?>

        </tbody>
    </table>
</div>

</div>