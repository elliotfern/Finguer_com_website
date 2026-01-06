<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";
?>

<h2>Estat 3: Reserves clients anuals completades amb check-out del parking</h2>
<h4>Ordenat segons data sortida vehicle</h4>

<?php

$sql = "
SELECT
    r.id,
    r.localizador,
    r.entrada_prevista,
    r.salida_prevista,
    r.vehiculo,
    r.matricula,
    r.vuelo,
    r.tipo,
    r.notas,
    r.estado,
    r.estado_vehiculo,
    u.nombre AS client_nombre,
    u.telefono AS client_telefono
FROM parking_reservas r
LEFT JOIN usuarios u ON u.id = r.usuario_id
WHERE
    u.tipo_rol = 'cliente_anual'
    AND r.estado = 'anual'
    AND r.estado_vehiculo = 'salido'
ORDER BY r.salida_prevista DESC
";

$pdo_statement = $conn->prepare($sql);
$pdo_statement->execute();
$result = $pdo_statement->fetchAll(PDO::FETCH_ASSOC);

function fmtFechaHora(?string $dt): array
{
    $dt = trim((string)($dt ?? ''));
    if ($dt === '' || $dt === '0000-00-00 00:00:00') {
        return ['Pendent', ''];
    }

    $ts = strtotime($dt);
    if (!$ts) {
        return ['Pendent', ''];
    }

    return [date('d-m-Y', $ts), date('H:i', $ts)];
}

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
                        <th>Entrada</th>
                        <th>Sortida &darr;</th>
                        <th>Vehicle</th>
                        <th>Vol tornada</th>
                        <th>Neteja</th>
                        <th>Accions</th>
                    </tr>
                </thead>
                <tbody>

                <?php
                foreach ($result as $row) {

                    $reservaId   = (int)$row['id'];
                    $localizador = (string)($row['localizador'] ?? '');

                    $matricula1 = (string)($row['matricula'] ?? '');
                    $modelo1    = (string)($row['vehiculo'] ?? '');
                    $vuelo1     = (string)($row['vuelo'] ?? '');

                    [$dataEntrada, $horaEntrada] = fmtFechaHora($row['entrada_prevista'] ?? null);
                    [$dataSortida, $horaSortida] = fmtFechaHora($row['salida_prevista'] ?? null);

                    $tipoRaw = (string)($row['tipo'] ?? '');
                    if ($tipoRaw === '2') {
                        $tipoReserva2 = "Gold Finguer Class";
                    } elseif ($tipoRaw === '1') {
                        $tipoReserva2 = "Finguer Class";
                    } elseif ($tipoRaw === '3') {
                        $tipoReserva2 = "Client anual";
                    } else {
                        $tipoReserva2 = $tipoRaw !== '' ? $tipoRaw : "Client anual";
                    }

                    $limpieza2 = "-";

                    $nom = (string)($row['client_nombre'] ?? '');
                    $telefon = (string)($row['client_telefono'] ?? '');

                    echo "<tr>";

                    echo "<td>";
                    echo "<button type='button' class='btn btn-primary btn-sm'>Client anual</button>";
                    if ($localizador !== '') {
                        echo "<div style='font-size:12px;opacity:.8'>Loc: " . htmlspecialchars($localizador, ENT_QUOTES) . "</div>";
                    }
                    echo "</td>";

                    echo "<td>" . htmlspecialchars($tipoReserva2, ENT_QUOTES) . "</td>";

                    echo "<td>" . htmlspecialchars($nom, ENT_QUOTES) . " // " . htmlspecialchars($telefon, ENT_QUOTES) . "</td>";

                    echo "<td>";
                    if ($dataEntrada === 'Pendent') {
                        echo "Pendent";
                    } else {
                        echo htmlspecialchars($dataEntrada, ENT_QUOTES) . " // " . htmlspecialchars($horaEntrada, ENT_QUOTES);
                    }
                    echo "</td>";

                    echo "<td>";
                    if ($dataSortida === 'Pendent') {
                        echo "Pendent";
                    } else {
                        echo htmlspecialchars($dataSortida, ENT_QUOTES) . " // " . htmlspecialchars($horaSortida, ENT_QUOTES);
                    }
                    echo "</td>";

                    echo "<td>" . htmlspecialchars($modelo1, ENT_QUOTES) . " // " . htmlspecialchars($matricula1, ENT_QUOTES) . "</td>";

                    echo "<td>";
                    if ($vuelo1 === '') {
                        // Mantengo el estilo "Afegir vol" pero ya no uso afegir-vol.php?; uso tu ruta nueva como en los otros listados
                        echo "<a href='" . APP_WEB . "/reserva/modificar/vol/" . $reservaId . "' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Afegir vol</a>";
                    } else {
                        echo htmlspecialchars($vuelo1, ENT_QUOTES);
                    }
                    echo "</td>";

                    echo "<td>" . htmlspecialchars($limpieza2, ENT_QUOTES) . "</td>";

                    echo "<td>Reserva completada</td>";

                    echo "</tr>";
                }

                echo "</tbody>";
                echo "</table>";
                echo "</div>";

                // contador
                $sql2 = "
                SELECT COUNT(*) AS numero
                FROM parking_reservas r
                JOIN usuarios u ON u.id = r.usuario_id
                WHERE
                    u.tipo_rol = 'cliente_anual'
                    AND r.estado = 'anual'
                    AND r.estado_vehiculo = 'salido'
                ";

                $st2 = $conn->prepare($sql2);
                $st2->execute();
                $numero = (int)$st2->fetchColumn();

                echo "<h5>Total reserves completades: " . $numero . " </h5>";
                echo "</div>";

            } else {
                echo "En aquests moments no hi ha cap reserva de client anual completada";
            }

echo "</div>";
?>
