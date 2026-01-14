<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// UUID en HEX(32) al final de la URL
if (!preg_match('#/client/([0-9a-fA-F]{32})$#', $path, $matches)) {
    http_response_code(400);
    die('UUID de cliente no encontrado');
}

$uuidHex = strtolower($matches[1]);

if (!preg_match('/^[0-9a-f]{32}$/', $uuidHex)) {
    http_response_code(400);
    die('UUID de cliente inválido');
}

global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container' style='margin-bottom:100px'>";
echo "<h3>Clients amb Abonament anual</h3>";
echo "<h4>Eliminació (desactivació) del client</h4>";

$codi_resposta = 1;

// 1) Cargar datos del cliente (para mostrar nombre y validar que existe)
$sql = "SELECT
            c.nombre,
            c.estado
        FROM usuarios AS c
        WHERE c.uuid = UNHEX(:uuid_hex)
          AND c.tipo_rol = 'cliente_anual'
        LIMIT 1";

$st = $conn->prepare($sql);
$st->bindValue(':uuid_hex', $uuidHex, PDO::PARAM_STR);
$st->execute();
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    die('Cliente no encontrado');
}

$nom_old = (string)($row['nombre'] ?? '');
$estado_actual = (string)($row['estado'] ?? '');

echo "<p><strong>Client:</strong> " . htmlspecialchars($nom_old, ENT_QUOTES) . "</p>";
echo "<p><strong>Estat actual:</strong> " . htmlspecialchars($estado_actual, ENT_QUOTES) . "</p>";

// 2) Si confirman, soft delete => estado='eliminado'
if (isset($_POST["remove-client"])) {

    // Si quieres evitar re-eliminar o cambios innecesarios:
    if ($estado_actual === 'eliminado') {
        $codi_resposta = 3; // ya estaba eliminado, lo tratamos como OK
    } else {
        $sql = "UPDATE usuarios
                SET estado = 'eliminado'
                WHERE uuid = UNHEX(:uuid_hex)
                  AND tipo_rol = 'cliente_anual'
                LIMIT 1";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uuid_hex', $uuidHex, PDO::PARAM_STR);

        if ($stmt->execute() && $stmt->rowCount() === 1) {
            $codi_resposta = 3;
            $estado_actual = 'eliminado';
        } else {
            $codi_resposta = 2;
        }
    }
}

// 3) Mensajes + formulario confirmación
if ($codi_resposta == 3) {
    echo '<div class="alert alert-success" role="alert">
            <h4 class="alert-heading"><strong>Operació realitzada correctament.</strong></h4>
            El client ha passat a estat <strong>eliminat</strong>.
          </div>';

    echo '<a href="' . APP_WEB . '/control/clients-anuals/" class="btn btn-outline-secondary menuBtn">Tornar</a>';
} elseif ($codi_resposta == 2) {
    echo '<div class="alert alert-danger" role="alert">
            <h4 class="alert-heading"><strong>Error</strong></h4>
            No s\'ha pogut actualitzar l\'estat del client.
          </div>';

    echo '<a href="' . APP_WEB . '/control/clients-anuals/" class="btn btn-outline-secondary menuBtn">Tornar</a>';
} else {
    // Confirmación
    echo '<form action="" method="post" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';
    echo '<div class="col-12">';
    echo '<h5>Estàs segur que vols marcar aquest client com a eliminat?</h5>';
    echo '<p class="mb-3">Això <strong>no</strong> esborra el registre, només canvia l\'estat a <code>eliminado</code>.</p>';
    echo '</div>';

    echo "<div class='col-12 d-flex flex-column flex-md-row justify-content-between gap-2'>";

    echo "<a href='" . APP_WEB . "/control/clients-anuals/' class='btn btn-outline-secondary menuBtn'>
            Tornar
          </a>";

    echo "<button id='remove-client' name='remove-client' type='submit' class='btn btn-danger'>
            Marcar com eliminat
          </button>";

    echo "</div>";
    echo "</form>";
}

echo '</div>';
