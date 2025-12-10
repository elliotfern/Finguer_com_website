<?php

if (isset($params['idClient'])) {
    $idClient = $params['idClient'];
} else {
    $idClient = "";
}

global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";

if (is_numeric($idClient)) {
    $idClient_old = intval($idClient);
} else {
    $idClient_old = NULL;
}

echo "<h3>Creació reserva de client Abonament anual</h3>";

$codi_resposta = 2;

if (isset($_POST["alta-reserva"])) {

    if (empty($_POST["idClient"])) {
        $hasError = true;
    } else {
        $idClient = data_input($_POST["idClient"], ENT_NOQUOTES);
    }

    if (empty($_POST["diaEntrada"])) {
        $diaEntrada = NULL;
    } else {
        $diaEntrada = data_input($_POST["diaEntrada"], ENT_NOQUOTES);
    }

    if (empty($_POST["horaEntrada"])) {
        $horaEntrada = NULL;
    } else {
        $horaEntrada = data_input($_POST["horaEntrada"], ENT_NOQUOTES);
    }

    if (empty($_POST["diaSalida"])) {
        $diaSalida = NULL;
    } else {
        $diaSalida = data_input($_POST["diaSalida"], ENT_NOQUOTES);
    }

    if (empty($_POST["horaSalida"])) {
        $horaSalida = NULL;
    } else {
        $horaSalida = data_input($_POST["horaSalida"], ENT_NOQUOTES);
    }

    if (empty($_POST["vuelo"])) {
        $vuelo = NULL;
    } else {
        $vuelo = data_input($_POST["vuelo"], ENT_NOQUOTES);
    }

    if (empty($_POST["notes"])) {
        $notes = NULL;
    } else {
        $notes = data_input($_POST["notes"], ENT_NOQUOTES);
    }

    if (empty($_POST["tipo"])) {
        $tipo = NULL;
    } else {
        $tipo = data_input($_POST["tipo"], ENT_NOQUOTES);
    }

    if (empty($_POST["vehiculo"])) {
        $vehiculo = NULL;
    } else {
        $vehiculo = data_input($_POST["vehiculo"], ENT_NOQUOTES);
    }

    if (empty($_POST["matricula"])) {
        $matricula = NULL;
    } else {
        $matricula = data_input($_POST["matricula"], ENT_NOQUOTES);
    }

    $idReserva = 0001;
    $neteja = NULL;
    $checkIn = 5;
    $checkOut = NULL;
    $fechaReserva = date("Y-m-d H:i:s");

    // Si no hi ha cap error, envia el formulari
    if (!isset($hasError)) {
        $emailSent = true;
    } else { // Error > bloqueja i mostra avis
        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
        echo 'Controla que totes les dades siguin correctes.</div>';
    }

    $sql = "INSERT INTO reserves_parking SET idClient=:idClient, idReserva=:idReserva, diaEntrada=:diaEntrada, horaEntrada=:horaEntrada, diaSalida=:diaSalida, horaSalida=:horaSalida, vuelo=:vuelo, notes=:notes, tipo=:tipo, checkIn=:checkIn, checkOut=:checkOut, matricula=:matricula, vehiculo=:vehiculo, fechaReserva=:fechaReserva";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":idClient", $idClient, PDO::PARAM_INT);
    $stmt->bindParam(":idReserva", $idReserva, PDO::PARAM_INT);
    $stmt->bindParam(":diaEntrada", $diaEntrada, PDO::PARAM_STR);
    $stmt->bindParam(":horaEntrada", $horaEntrada, PDO::PARAM_STR);
    $stmt->bindParam(":diaSalida", $diaSalida, PDO::PARAM_STR);
    $stmt->bindParam(":horaSalida", $horaSalida, PDO::PARAM_STR);
    $stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
    $stmt->bindParam(":notes", $notes, PDO::PARAM_STR);
    $stmt->bindParam(":tipo", $tipo, PDO::PARAM_INT);
    $stmt->bindParam(":checkIn", $checkIn, PDO::PARAM_INT);
    $stmt->bindParam(":checkOut", $checkOut, PDO::PARAM_INT);
    $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
    $stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
    $stmt->bindParam(":fechaReserva", $fechaReserva, PDO::PARAM_STR);

    if ($stmt->execute()) {
        $codi_resposta = 1;
    } else {
        $codi_resposta = 2;
    }

    if ($codi_resposta == 1) {
        echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Alta reserva realitzada correctament.</strong></h4>';
        echo "Alta reserva amb èxit.</div>";
    } else { // Error > bloqueja i mostra avis
        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
        echo 'Les dades no s\'han transmès correctament.</div>';
    }
}

if ($codi_resposta == 2) {
    echo '<form action="" method="post" id="alta-reserva" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

    echo '<input type="hidden" name="idReserva" value="0001" />';

    echo "<h5>Selecciona un client (camp obligatori):</h5>";

    echo '<div class="col-md-4">';
    echo '<label>Nom client:</label>';
    echo '<select class="form-select" name="idClient" id="idClient">';
    echo '<option selected disabled>Selecciona el client:</option>';
    // consulta general reserves 
    $sql = "SELECT c.nombre, c.id
                    FROM usuaris AS c
                    WHERE tipoUsuario = 3
                    ORDER BY c.nombre ASC";

    $pdo_statement = $conn->prepare($sql);
    $pdo_statement->execute();
    $result = $pdo_statement->fetchAll();
    foreach ($result as $row) {
        $nom_old = $row['nombre'];
        $id_old = $row['id'];
        if ($idClient_old == $id_old) {
            echo "<option value=" . $idClient_old . " selected>" . $nom_old . "</option>";
        } else {
            echo "<option value=" . $id_old . ">" . $nom_old . "</option>";
        }
    }
    echo '</select>';
    echo "</div>";

    echo "<hr>";
    echo "<h5>Aquests camps són opcionals, els pots modificar més endavant:</h5>";


    echo '<div class="col-md-4">';
    echo '<label>Tipo reserva:</label>';
    echo '<select class="form-select" name="tipo" id="tipo">';
    echo '<option selected disabled>Selecciona una opció:</option>';
    echo "<option value='1' selected>Finguer class</option>";
    echo "<option value='2'>Gold Finguer Class</option>";
    echo '</select>';
    echo "</div>";

    echo '<div class="col-md-4">';
    echo '<label>Data entrada:</label>';
    echo '<input type="date" class="form-control" id="diaEntrada" name="diaEntrada">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Hora entrada:</label>';
    echo '<input type="text" class="form-control" id="horaEntrada" name="horaEntrada" placeholder="00:00">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Data sortida:</label>';
    echo '<input type="date" class="form-control" id="diaSalida" name="diaSalida">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Hora sortida:</label>';
    echo '<input type="text" class="form-control" id="horaSalida" name="horaSalida" placeholder="00:00">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Vol:</label>';
    echo '<input type="text" class="form-control" id="vuelo" name="vuelo">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Notes:</label>';
    echo '<input type="text" class="form-control" id="notes" name="notes">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Model cotxe:</label>';
    echo '<input type="text" class="form-control" id="vehiculo" name="vehiculo">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Matrícula:</label>';
    echo '<input type="text" class="form-control" id="matricula" name="matricula">';
    echo '</div>';

    echo "<div class='md-12'>";
    echo "<button id='alta-reserva' name='alta-reserva' type='submit' class='btn btn-primary'>Alta reserva</button><a href='" . APP_WEB . "/clients-anuals/crear/reserva/" . $idClient_old . "'></a>
                    </div>";

    echo "</form>";
} else {
    echo '<a href="' . APP_WEB . '/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
}

echo '</div>
                </div>';

echo "</div>";
