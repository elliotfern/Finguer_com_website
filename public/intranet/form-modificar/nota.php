<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
$id = $routeParams[0];

if (is_numeric($id)) {
    $id_old = intval($id);

    if (filter_var($id_old, FILTER_VALIDATE_INT)) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.localizador, r.notas, r.id
        FROM parking_reservas AS r
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach ($result as $row) {
            $idReserva_old = $row['localizador'];
            $notes_old = $row['notas'];
        }

        echo "<div class='container' style='margin-bottom:50px'>";

        if ($idReserva_old == 1) {
            echo "<h3>Afegir/modificar una nota al client anual amb ID: " . $id_old . "</h3>";
        } else {
            echo "<h2>Afegir/modificar una nota a la reserva núm: " . $idReserva_old . " </h2>";
        }

        if (isset($_POST["add-nota"])) {
            global $pdo_conn;

            if (empty($_POST["notas"])) {
                $notas = data_input($_POST["notas"], ENT_NOQUOTES);
            } else {
                $notas = data_input($_POST["notas"], ENT_NOQUOTES);
            }

            // Si no hi ha cap error, envia el formulari
            if (!isset($hasError)) {
                $emailSent = true;
            } else { // Error > bloqueja i mostra avis
                echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</h4></strong>';
                echo 'Controla que totes les dades siguin correctes.</div>';
            }

            $sql = "UPDATE parking_reservas SET notas=:notas
                    WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":notas", $notas, PDO::PARAM_STR);
            $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $codi_resposta = 1;
            } else {
                $codi_resposta = 2;
            }

            if ($codi_resposta == 1) {
                echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Nota afegida correctament.</strong></h4>';
                echo "Nota afegida.</div>";
            } else { // Error > bloqueja i mostra avis
                echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
                echo 'Les dades no s\'han transmès correctament.</div>';
            }
        }

        if ($codi_resposta == 2) {
            echo '<form action="" method="post" id="add-nota" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

            echo '<div class="col-md-4">';
            echo '<label>Nota reserva:</label>';
            echo '<input type="text" class="form-control" id="notas" name="notas" value="' . $notes_old . '">';
            echo '</div>';

            echo "<div class='md-12'>";
            echo "<button id='add-nota' name='add-nota' type='submit' class='btn btn-primary'>Modificar nota</button><a href='" . APP_SERVER . "/reserva/modificar/nota/" . $id_old . "'></a>
                    </div>";

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
