<?php
global $conn;

require_once APP_ROOT . '/public/intranet/inc/header.php';

$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);

    if (filter_var($id_old, FILTER_VALIDATE_INT)) {
        $codi_resposta = 2;

        // =========================
        // 1) SELECT con nueva tabla
        // =========================

        $sql = "
            SELECT 
                r.id                AS idReserva,
                r.usuario_id        AS idClient,
                r.total_calculado   AS importe,
                r.estado            AS estado,
                r.fecha_reserva     AS fechaReserva,
                r.tipo              AS tipo,
                u.nombre            AS nombre,
                r.entrada_prevista  AS entrada_prevista,
                r.salida_prevista   AS salida_prevista,
                r.vehiculo          AS vehiculo,
                r.matricula         AS matricula,
                r.vuelo             AS vuelo,
                r.notas             AS notas,
                r.canal             AS canal
            FROM epgylzqu_parking_finguer_v2.parking_reservas AS r
            LEFT JOIN epgylzqu_parking_finguer_v2.usuarios AS u ON r.usuario_id = u.id
            WHERE r.id = :id
            LIMIT 1
        ";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->bindParam(':id', $id_old, PDO::PARAM_INT);
        $pdo_statement->execute();
        $row = $pdo_statement->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            $idReserva_old    = $row['idReserva'];
            $idClient_old     = $row['idClient'];
            $importe_old      = $row['importe'];
            $estado_old       = $row['estado'];
            $fechaReserva_old = $row['fechaReserva'];
            $tipo_old         = $row['tipo'];
            $nombre_old       = $row['nombre'];

            $entrada_prevista_old = $row['entrada_prevista'];
            $salida_prevista_old  = $row['salida_prevista'];

            // Spliteamos las datetimes en fecha / hora para el formulario
            $diaEntrada_old  = $entrada_prevista_old ? date('Y-m-d', strtotime($entrada_prevista_old)) : '';
            $horaEntrada_old = $entrada_prevista_old ? date('H:i', strtotime($entrada_prevista_old)) : '';

            $diaSalida_old   = $salida_prevista_old ? date('Y-m-d', strtotime($salida_prevista_old)) : '';
            $horaSalida_old  = $salida_prevista_old ? date('H:i', strtotime($salida_prevista_old)) : '';

            $vehiculo_old    = $row['vehiculo'];
            $matricula_old   = $row['matricula'];
            $vuelo_old       = $row['vuelo'];
            $notes_old       = $row['notas'];
            $canal_old       = $row['canal'];
        } else {
            echo "<div class='container'><div class='alert alert-danger'>No s'ha trobat aquesta reserva a la base de dades nova.</div></div>";
            exit;
        }

        if ($fechaReserva_old !== null) {
            $fecha_formateada = "<h4>Reserva efectuada el dia: " . date('d-m-Y H:i:s', strtotime($fechaReserva_old)) . "</h4>";
        } else {
            $fecha_formateada = "";
        }

        echo "<div class='container' style='margin-bottom:50px'>
        <h2>Modificació ID Reserva: " . htmlspecialchars((string)$idReserva_old) . " </h2>";
        echo $fecha_formateada;

        // ==================================
        // 2) Procesar POST (UPDATE nueva BD)
        // ==================================
        if (isset($_POST["update"])) {

            // Sanitizar / recoger datos
            $importe     = data_input($_POST["importe"] ?? '');
            $tipo        = data_input($_POST["tipo"] ?? '');
            $estado      = data_input($_POST["estado"] ?? '');
            $horaEntrada = data_input($_POST["horaEntrada"] ?? '');
            $diaEntrada  = data_input($_POST["diaEntrada"] ?? '');
            $horaSalida  = data_input($_POST["horaSalida"] ?? '');
            $diaSalida   = data_input($_POST["diaSalida"] ?? '');
            $vehiculo    = data_input($_POST["vehiculo"] ?? '');
            $matricula   = data_input($_POST["matricula"] ?? '');
            $vuelo       = data_input($_POST["vuelo"] ?? '');
            $notes       = data_input($_POST["notes"] ?? '');

            // Construir datetimes entrada/salida
            $entrada_prevista = null;
            if (!empty($diaEntrada)) {
                $entrada_prevista = $diaEntrada . (empty($horaEntrada) ? ' 00:00:00' : ' ' . $horaEntrada . ':00');
            }

            $salida_prevista = null;
            if (!empty($diaSalida)) {
                $salida_prevista = $diaSalida . (empty($horaSalida) ? ' 00:00:00' : ' ' . $horaSalida . ':00');
            }

            // Aquí podrías validar y setear $hasError si algo está mal

            if (!isset($hasError)) {

                $sqlUpdate = "
                    UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                    SET 
                        total_calculado  = :importe,
                        tipo             = :tipo,
                        estado           = :estado,
                        entrada_prevista = :entrada_prevista,
                        salida_prevista  = :salida_prevista,
                        vehiculo         = :vehiculo,
                        matricula        = :matricula,
                        vuelo            = :vuelo,
                        notas            = :notes
                    WHERE id = :id
                ";

                $stmt = $conn->prepare($sqlUpdate);

                // total_calculado es DECIMAL → PARAM_STR va bien
                $stmt->bindParam(":importe", $importe, PDO::PARAM_STR);
                $stmt->bindParam(":tipo", $tipo, PDO::PARAM_STR);
                $stmt->bindParam(":estado", $estado, PDO::PARAM_STR);
                $stmt->bindParam(":entrada_prevista", $entrada_prevista, PDO::PARAM_STR);
                $stmt->bindParam(":salida_prevista", $salida_prevista, PDO::PARAM_STR);
                $stmt->bindParam(":vehiculo", $vehiculo, PDO::PARAM_STR);
                $stmt->bindParam(":matricula", $matricula, PDO::PARAM_STR);
                $stmt->bindParam(":vuelo", $vuelo, PDO::PARAM_STR);
                $stmt->bindParam(":notes", $notes, PDO::PARAM_STR);
                $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $codi_resposta = 1;
                } else {
                    $codi_resposta = 2;
                }

                if ($codi_resposta == 1) {
                    echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Reserva actualitzada correctament.</strong></h4>';
                    echo "Reserva actualitzada.</div>";
                } else {
                    echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                    echo "Les dades no s\'han transmès correctament.</div>";
                }
            } else {
                echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                echo 'Controla que totes les dades siguin correctes.</div>';
            }
        }

        // =======================
        // 3) Formulario de edición
        // =======================
        if ($codi_resposta == 2) {
            echo '<form action="" method="post" id="update" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

            echo '<h4>Dades de la reserva:</h4>';

            // Nombre cliente
            echo '<div class="col-md-4">';
            echo '<label>Nom client:</label>';
            echo '<input type="text" class="form-control" id="nombre" readonly value="' . htmlspecialchars((string)$nombre_old) . '">';
            echo '<small class="form-text text-danger">* No es pot modificar</small>';
            echo '</div>';

            // Importe
            echo '<div class="col-md-4">';
            echo '<label>Import reserva:</label>';
            echo '<input type="text" class="form-control" id="importe" readonly name="importe" value="' . htmlspecialchars((string)$importe_old) . '">';
            echo '<small class="form-text text-danger">* No es pot modificar</small>';
            echo '</div>';

            // Tipus reserva (campo tipo varchar(50))
            echo '<div class="col-md-4">';
            echo '<label>Tipus de reserva:</label>';
            echo '<select class="form-select" name="tipo" id="tipo">';
            echo '<option disabled>Selecciona una opció:</option>';

            $tipos = [
                '1' => 'Finguer Class',
                '2'  => 'Gold Finguer',
                '3'  => 'Finguer Class Anual',
            ];

            foreach ($tipos as $valor => $etiqueta) {
                $selected = ($tipo_old === $valor) ? " selected" : "";
                echo "<option value='" . htmlspecialchars($valor) . "'" . $selected . ">" . htmlspecialchars($etiqueta) . "</option>";
            }

            echo '</select>';
            echo '</div>';

            // Estado (enum: 'pendiente', 'pagada', 'cancelada', 'anual')
            echo '<div class="col-md-4">';
            echo '<label>Estat de la reserva:</label>';
            echo '<select class="form-select" name="estado" id="estado">';
            echo '<option disabled>Selecciona una opció:</option>';

            $estados = [
                'pendiente' => 'Pendent',
                'pagada'    => 'Pagada',
                'pago_oficina'  => 'Pago en oficina',
                'cancelada' => 'Cancel·lada',
                'anual'     => 'Anual',
            ];

            foreach ($estados as $valor => $etiqueta) {
                $selected = ($estado_old === $valor) ? " selected" : "";
                echo "<option value='" . htmlspecialchars($valor) . "'" . $selected . ">" . htmlspecialchars($etiqueta) . "</option>";
            }

            echo '</select>';
            echo '</div>';

            echo "<hr>";
            echo "<h4>Entrada i sortida:</h4>";

            // Día entrada
            echo '<div class="col-md-6">';
            echo '<label>Dia entrada:</label>';
            echo '<input type="date" class="form-control" id="diaEntrada" name="diaEntrada" value="' . htmlspecialchars((string)$diaEntrada_old) . '">';
            echo '</div>';

            // Hora entrada
            echo '<div class="col-md-6">';
            echo '<label>Hora entrada:</label>';
            echo '<input type="time" class="form-control" id="horaEntrada" name="horaEntrada" value="' . htmlspecialchars((string)$horaEntrada_old) . '">';
            echo '</div>';

            // Día salida
            echo '<div class="col-md-6">';
            echo '<label>Dia sortida:</label>';
            echo '<input type="date" class="form-control" id="diaSalida" name="diaSalida" value="' . htmlspecialchars((string)$diaSalida_old) . '">';
            echo '</div>';

            // Hora salida
            echo '<div class="col-md-6">';
            echo '<label>Hora sortida:</label>';
            echo '<input type="time" class="form-control" id="horaSalida" name="horaSalida" value="' . htmlspecialchars((string)$horaSalida_old) . '">';
            echo '</div>';

            echo "<hr>";

            // Vehículo
            echo '<div class="col-md-4">';
            echo '<label>Model vehicle:</label>';
            echo '<input type="text" class="form-control" id="vehiculo" name="vehiculo" value="' . htmlspecialchars((string)$vehiculo_old) . '">';
            echo '</div>';

            // Matrícula
            echo '<div class="col-md-4">';
            echo '<label>Número de matrícula:</label>';
            echo '<input type="text" class="form-control" id="matricula" name="matricula" value="' . htmlspecialchars((string)$matricula_old) . '">';
            echo '</div>';

            // Vuelo
            echo '<div class="col-md-4">';
            echo '<label>Vol client:</label>';
            echo '<input type="text" class="form-control" id="vuelo" name="vuelo" value="' . htmlspecialchars((string)$vuelo_old) . '">';
            echo '</div>';

            // Notas
            echo '<div class="col-md-12">';
            echo '<label>Nota reserva:</label>';
            echo '<input type="text" class="form-control" id="notes" name="notes" value="' . htmlspecialchars((string)$notes_old) . '">';
            echo '</div>';

            echo "<div class='md-12' style='margin-top:15px;'>";
            echo "<button id='update' name='update' type='submit' class='btn btn-primary'>Modificar reserva</button>";
            echo "</div>";

            echo "</form>";
        } else {
            echo '<a href="' . APP_WEB . '/inici" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
        }
    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";
