<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";
?>

<h2>Estat 2: Reserves clients anuals al parking</h2>
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
    r.canal,
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
    AND r.estado_vehiculo = 'dentro'
ORDER BY r.salida_prevista ASC
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

                    $reservaId   = (int)$row['id'];
                    $localizador = (string)($row['localizador'] ?? '');

                    $matricula1 = (string)($row['matricula'] ?? '');
                    $modelo1    = (string)($row['vehiculo'] ?? '');
                    $vuelo1     = (string)($row['vuelo'] ?? '');

                    [$dataEntrada, $horaEntrada] = fmtFechaHora($row['entrada_prevista'] ?? null);
                    [$dataSortida, $horaSortida] = fmtFechaHora($row['salida_prevista'] ?? null);

                    // tipo ahora varchar; anual suele ser "3"
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

                    $limpieza2 = "-"; // ya no existe en la tabla nueva
                    $notes = (string)($row['notas'] ?? '');

                    $nom = (string)($row['client_nombre'] ?? '');
                    $telefon = (string)($row['client_telefono'] ?? '');

                    $estadoVehiculo = (string)($row['estado_vehiculo'] ?? '');

                    echo "<tr>";

                    // Reserva
                    echo "<td>";
                    echo "<button type='button' class='btn btn-primary btn-sm'>Client anual</button>";
                    if ($localizador !== '') {
                        echo "<div style='font-size:12px;opacity:.8'>Loc: " . htmlspecialchars($localizador, ENT_QUOTES) . "</div>";
                    }
                    echo "</td>";

                    // Tipus
                    echo "<td>" . htmlspecialchars($tipoReserva2, ENT_QUOTES) . "</td>";

                    // Client // tel
                    echo "<td>" . htmlspecialchars($nom, ENT_QUOTES) . " // " . htmlspecialchars($telefon, ENT_QUOTES) . "</td>";

                    // Entrada
                    echo "<td>";
                    if ($dataEntrada === 'Pendent') {
                        echo "Pendent";
                    } else {
                        echo htmlspecialchars($dataEntrada, ENT_QUOTES) . " // " . htmlspecialchars($horaEntrada, ENT_QUOTES);
                    }
                    echo "</td>";

                    // Sortida
                    echo "<td>";
                    if ($dataSortida === 'Pendent') {
                        echo "Pendent";
                    } else {
                        echo htmlspecialchars($dataSortida, ENT_QUOTES) . " // " . htmlspecialchars($horaSortida, ENT_QUOTES);
                    }
                    echo "</td>";

                    // Vehicle // matricula
                    echo "<td>" . htmlspecialchars($modelo1, ENT_QUOTES) . " // <a href='" . APP_WEB . "/reserva/modificar/vehicle/" . $reservaId . "'>" . htmlspecialchars($matricula1, ENT_QUOTES) . "</a></td>";

                    // Vuelo
                    echo "<td>";
                    if ($vuelo1 === '') {
                        echo "<a href='" . APP_WEB . "/reserva/modificar/vol/" . $reservaId . "' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Afegir vol</a>";
                    } else {
                        echo "<a href='" . APP_WEB . "/reserva/modificar/vol/" . $reservaId . "'>" . htmlspecialchars($vuelo1, ENT_QUOTES) . "</a>";
                    }
                    echo "</td>";

                    // Neteja
                    echo "<td>" . htmlspecialchars($limpieza2, ENT_QUOTES) . "</td>";

                    // Accions (Check-Out)
                    echo "<td>";
                    if ($estadoVehiculo === 'dentro') {
                        echo "<a href='" . APP_WEB . "/reserva/fer/check-out/" . $reservaId . "' class='btn btn-secondary btn-sm' role='button' aria-pressed='true'>Check-Out</a>";
                    }
                    echo "</td>";

                    // Notes
                    echo "<td>";
                    if ($notes === '') {
                        echo "<a href='" . APP_WEB . "/reserva/modificar/nota/" . $reservaId . "' class='btn btn-info btn-sm' role='button' aria-pressed='true'>Crear notes</a>";
                    } else {
                        echo "<a href='" . APP_WEB . "/reserva/modificar/nota/" . $reservaId . "' class='btn btn-danger btn-sm' role='button' aria-pressed='true'>Veure_
