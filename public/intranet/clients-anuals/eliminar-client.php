<?php
$idClient = $params['idClient'];

global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container' style='margin-bottom:100px'>";
echo "<h3>Modificar dades client Abonament anual</h3>";

if (is_numeric($idClient)) {
    $idClient_old = intval($idClient);

    if (filter_var($idClient_old, FILTER_VALIDATE_INT)) {
        $codi_resposta = 1;

        // consulta general reserves 
        $sql = "SELECT c.nombre
        FROM usuarios AS c
        WHERE c.id=$idClient_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach ($result as $row) {
            $nom_old = $row['nombre'];
        }

        echo "<h4>Client: " . $nom_old . " </h4>";
        echo "<h5>Eliminació del client abonament anual</h5>";

        if (isset($_POST["remove-client"])) {
            $emailSent = true;
            $sql = "DELETE FROM usuarios
                            WHERE id=:id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(":id", $idClient_old, PDO::PARAM_INT);

            if ($stmt->execute()) {
                $codi_resposta = 3;
            } else {
                $codi_resposta = 2;
            }
        }

        if ($codi_resposta == 3) {
            echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Eliminació realizada correctament.</strong></h4>';
            echo "Eliminació del client anual amb èxit.</div>";
        } elseif ($codi_resposta == 2) {
            echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
            echo 'Les dades no s\'han transmès correctament.</div>';
        }

        if ($codi_resposta == 1) {
            echo '<form action="" method="post" id="remove-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

            echo "<hr>";
            echo "<h4>Estàs segur que vols eliminar aquest client?</h4>";
            echo '<form method="post" action="">';

            echo "<div class='md-12'>";
            echo "<button id='remove-client' name='remove-client' type='submit' class='btn btn-primary'>Eliminar client</button><a href='" . APP_WEB . "/control/clients-anuals/eliminar/client/" . $idClient_old . "'></a>
                            </div>";

            echo "</form>";
        } else {
            echo '<a href="' . APP_WEB . '/control/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
        }
    } else {
        echo "Error: aquest ID no és vàlid";
    }
}

echo '</div>';

echo "</div>";
