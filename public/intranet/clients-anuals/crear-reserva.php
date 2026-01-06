<?php

date_default_timezone_set('Europe/Rome');

$idClient = $params['idClient'] ?? "";
$idClient_old = is_numeric($idClient) ? (int)$idClient : null;

global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container' style='margin-bottom:50px'>";
echo "<h3>Creació reserva de client Abonament anual</h3>";

$codi_resposta = 2;

function parseDiaHoraToDatetimeOrNull(?string $dia, ?string $hora): ?string
{
    $dia  = trim((string)($dia ?? ''));
    $hora = trim((string)($hora ?? ''));

    if ($dia === '' || $hora === '') {
        return null;
    }

    $dt = DateTimeImmutable::createFromFormat('Y-m-d H:i', $dia . ' ' . $hora);
    if (!$dt) {
        return null;
    }

    return $dt->format('Y-m-d H:i:s');
}

if (isset($_POST["alta-reserva"])) {

    // --- Obligatorio: cliente ---
    if (empty($_POST["idClient"]) || !ctype_digit((string)$_POST["idClient"])) {
        $hasError = true;
    } else {
        $idClient = (int)$_POST["idClient"];
    }

    // --- Obligatorio: entrada (porque entrada_prevista es NOT NULL) ---
    $diaEntrada  = $_POST["diaEntrada"] ?? '';
    $horaEntrada = $_POST["horaEntrada"] ?? '';

    $entrada_prevista = parseDiaHoraToDatetimeOrNull($diaEntrada, $horaEntrada);
    if ($entrada_prevista === null) {
        $hasError = true;
    }

    // --- Opcional: salida (si falta => 2030...) ---
    $diaSalida  = $_POST["diaSalida"] ?? '';
    $horaSalida = $_POST["horaSalida"] ?? '';

    $salida_prevista = parseDiaHoraToDatetimeOrNull($diaSalida, $horaSalida) ?? '2030-01-01 00:00:00';

    // --- Opcionales ---
    $vuelo     = empty($_POST["vuelo"]) ? null : data_input($_POST["vuelo"], ENT_NOQUOTES);
    $notas     = empty($_POST["notes"]) ? null : data_input($_POST["notes"], ENT_NOQUOTES); // notes => notas
    $vehiculo  = empty($_POST["vehiculo"]) ? null : data_input($_POST["vehiculo"], ENT_NOQUOTES);
    $matricula = empty($_POST["matricula"]) ? null : data_input($_POST["matricula"], ENT_NOQUOTES);

    $fechaReserva = date("Y-m-d H:i:s");

    // Para generarLocalizador: usar tu función lista
    $dtEntrada = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $entrada_prevista);
    $localizador = generarLocalizador($conn, $dtEntrada ?: null);

    // Valores fijos para anual
    $estado = "anual";
    $estado_vehiculo = "pendiente_entrada";

    // Indicación tuya: mantener 3 y 5
    // (en DB son varchar ahora, así que guardamos "3" y "5")
    $tipo = "3";
    $canal = "5";

    if (!isset($hasError)) {

        $sql = "INSERT INTO parking_reservas SET
            usuario_id = :usuario_id,
            localizador = :localizador,
            estado = :estado,
            estado_vehiculo = :estado_vehiculo,
            fecha_reserva = :fecha_reserva,
            entrada_prevista = :entrada_prevista,
            salida_prevista = :salida_prevista,
            vehiculo = :vehiculo,
            matricula = :matricula,
            tipo = :tipo,
            vuelo = :vuelo,
            notas = :notas,
            canal = :canal,
            subtotal_calculado = NULL,
            iva_calculado = NULL,
            total_calculado = NULL
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":usuario_id", $idClient, PDO::PARAM_INT);
        $stmt->bindValue(":localizador", $localizador, PDO::PARAM_STR);

        $stmt->bindValue(":estado", $estado, PDO::PARAM_STR);
        $stmt->bindValue(":estado_vehiculo", $estado_vehiculo, PDO::PARAM_STR);

        $stmt->bindValue(":fecha_reserva", $fechaReserva, PDO::PARAM_STR);
        $stmt->bindValue(":entrada_prevista", $entrada_prevista, PDO::PARAM_STR);
        $stmt->bindValue(":salida_prevista", $salida_prevista, PDO::PARAM_STR);

        $stmt->bindValue(":vehiculo", $vehiculo, $vehiculo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":matricula", $matricula, $matricula === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->bindValue(":tipo", $tipo, PDO::PARAM_STR);
        $stmt->bindValue(":vuelo", $vuelo, $vuelo === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":notas", $notas, $notas === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":canal", $canal, PDO::PARAM_STR);

        if ($stmt->execute()) {
            $codi_resposta = 1;
        } else {
            $codi_resposta = 2;
        }

        if ($codi_resposta == 1) {
            echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Alta reserva realitzada correctament.</strong></h4>';
            echo "Alta reserva amb èxit.</div>";
        } else {
            echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
            echo 'Les dades no s\'han transmès correctament.</div>';
        }

    } else {
        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
        echo 'Controla que el client i la data/hora d\'entrada siguin correctes (obligatoris).</div>';
    }
}

if ($codi_resposta == 2) {

    $post = $_POST ?? [];

    echo '<form action="" method="post" id="alta-reserva" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

    echo "<h5>Selecciona un client (camp obligatori):</h5>";

    echo '<div class="col-md-6">';
    echo '<label>Nom client (*):</label>';
    echo '<select class="form-select" name="idClient" id="idClient" required>';
    echo '<option value="" disabled ' . (empty($post['idClient']) ? 'selected' : '') . '>Selecciona el client:</option>';

    $sql = "SELECT c.nombre, c.id
            FROM usuarios AS c
            WHERE c.tipo_rol = 'cliente_anual'
            ORDER BY c.nombre ASC";

    $pdo_statement = $conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll(PDO::FETCH_ASSOC);

    $selectedClient = $post['idClient'] ?? ($idClient_old ?? '');

    foreach ($result as $row) {
        $nom = $row['nombre'];
        $id  = (int)$row['id'];
        $selected = ((string)$selectedClient === (string)$id) ? 'selected' : '';
        echo "<option value=\"$id\" $selected>" . htmlspecialchars($nom, ENT_QUOTES) . "</option>";
    }

    echo '</select>';
    echo "</div>";

    echo "<hr>";
    echo "<h5>Aquests camps són opcionals, els pots modificar més endavant:</h5>";

    // Mantenemos el select solo a nivel UI si lo quieres, pero NO lo usaremos en backend (siempre tipo=3)
    // Si prefieres ocultarlo, lo quitamos.
    echo '<div class="col-md-4">';
    echo '<label>Tipo reserva:</label>';
    echo '<select class="form-select" name="tipo_ui" id="tipo_ui" disabled>';
    echo "<option value='3' selected>Client anual (3)</option>";
    echo '</select>';
    echo "</div>";

    echo '<div class="col-md-4">';
    echo '<label>Data entrada (*):</label>';
    echo '<input type="date" class="form-control" id="diaEntrada" name="diaEntrada" required value="' . htmlspecialchars($post['diaEntrada'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Hora entrada (*):</label>';
    echo '<input type="time" class="form-control" id="horaEntrada" name="horaEntrada" required value="' . htmlspecialchars($post['horaEntrada'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Data sortida:</label>';
    echo '<input type="date" class="form-control" id="diaSalida" name="diaSalida" value="' . htmlspecialchars($post['diaSalida'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Hora sortida:</label>';
    echo '<input type="time" class="form-control" id="horaSalida" name="horaSalida" value="' . htmlspecialchars($post['horaSalida'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Vol:</label>';
    echo '<input type="text" class="form-control" id="vuelo" name="vuelo" value="' . htmlspecialchars($post['vuelo'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-6">';
    echo '<label>Notes:</label>';
    echo '<input type="text" class="form-control" id="notes" name="notes" value="' . htmlspecialchars($post['notes'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-3">';
    echo '<label>Model cotxe:</label>';
    echo '<input type="text" class="form-control" id="vehiculo" name="vehiculo" value="' . htmlspecialchars($post['vehiculo'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-3">';
    echo '<label>Matrícula:</label>';
    echo '<input type="text" class="form-control" id="matricula" name="matricula" value="' . htmlspecialchars($post['matricula'] ?? '', ENT_QUOTES) . '">';
    echo '</div>';

    echo "<div class='col-12'>";
    echo "<button id='alta-reserva' name='alta-reserva' type='submit' class='btn btn-primary'>Alta reserva</button> ";
    echo '<a href="' . APP_WEB . '/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
    echo "</div>";

    echo "</form>";

} else {
    echo '<a href="' . APP_WEB . '/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
}

echo '</div></div>';
echo "</div>";
