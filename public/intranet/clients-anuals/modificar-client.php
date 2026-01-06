<?php

$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

if (!preg_match('#/client/([0-9]+)$#', $path, $matches)) {
    http_response_code(400);
    die('ID de cliente no encontrado');
}

$idClient = (int)$matches[1];
if ($idClient <= 0) {
    http_response_code(400);
    die('ID de cliente inválido');
}

global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";
echo "<h3>Modificar dades client Abonament anual</h3>";

$codi_resposta = 2;

// 1) Cargar datos actuales del cliente
$sql = "SELECT
            c.id,
            c.nombre,
            c.telefono,
            c.anualitat,
            c.locale,
            c.email,
            c.empresa,
            c.nif,
            c.direccion,
            c.ciudad,
            c.codigo_postal,
            c.pais
        FROM usuarios AS c
        WHERE c.id = :id
        LIMIT 1";
$st = $conn->prepare($sql);
$st->execute([':id' => $idClient]);
$row = $st->fetch(PDO::FETCH_ASSOC);

if (!$row) {
    http_response_code(404);
    die('Cliente no encontrado');
}

echo "<h4>Client: " . htmlspecialchars((string)$row['nombre'], ENT_QUOTES) . " </h4>";

// Valores actuales (para pintar el form)
$nom_old           = (string)($row['nombre'] ?? '');
$telefon_old        = $row['telefono'] ?? null;
$anualitat_old      = $row['anualitat'] ?? null;

$locale_old         = (string)($row['locale'] ?? 'ca');
$email_old          = $row['email'] ?? null;
$empresa_old        = $row['empresa'] ?? null;
$nif_old            = $row['nif'] ?? null;
$direccion_old      = $row['direccion'] ?? null;
$ciudad_old         = $row['ciudad'] ?? null;
$codigo_postal_old  = $row['codigo_postal'] ?? null;
$pais_old           = $row['pais'] ?? null;

// 2) Procesar update
if (isset($_POST["update-client"])) {

    // nombre obligatorio
    if (empty($_POST["nombre"])) {
        $hasError = true;
    } else {
        $nombre = data_input($_POST["nombre"], ENT_NOQUOTES);
    }

    // locale obligatorio (enum)
    $localesPermitidos = ['ca', 'es', 'fr', 'en', 'it'];
    if (empty($_POST["locale"]) || !in_array($_POST["locale"], $localesPermitidos, true)) {
        $hasError = true;
    } else {
        $locale = $_POST["locale"];
    }

    // opcionales
    $telefono       = empty($_POST["telefono"]) ? null : data_input($_POST["telefono"], ENT_NOQUOTES);
    $anualitat      = empty($_POST["anualitat"]) ? null : data_input($_POST["anualitat"], ENT_NOQUOTES);

    $email          = empty($_POST["email"]) ? null : data_input($_POST["email"], ENT_NOQUOTES);
    $empresa        = empty($_POST["empresa"]) ? null : data_input($_POST["empresa"], ENT_NOQUOTES);
    $nif            = empty($_POST["nif"]) ? null : data_input($_POST["nif"], ENT_NOQUOTES);
    $direccion      = empty($_POST["direccion"]) ? null : data_input($_POST["direccion"], ENT_NOQUOTES);
    $ciudad         = empty($_POST["ciudad"]) ? null : data_input($_POST["ciudad"], ENT_NOQUOTES);
    $codigo_postal  = empty($_POST["codigo_postal"]) ? null : data_input($_POST["codigo_postal"], ENT_NOQUOTES);
    $pais           = empty($_POST["pais"]) ? null : data_input($_POST["pais"], ENT_NOQUOTES);

    if (!isset($hasError)) {

        $sql = "UPDATE usuarios SET
                    nombre = :nombre,
                    telefono = :telefono,
                    anualitat = :anualitat,
                    locale = :locale,
                    email = :email,
                    empresa = :empresa,
                    nif = :nif,
                    direccion = :direccion,
                    ciudad = :ciudad,
                    codigo_postal = :codigo_postal,
                    pais = :pais
                WHERE id = :id
                LIMIT 1";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":id", $idClient, PDO::PARAM_INT);
        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);
        $stmt->bindValue(":locale", $locale, PDO::PARAM_STR);

        $stmt->bindValue(":telefono", $telefono, $telefono === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":anualitat", $anualitat, $anualitat === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->bindValue(":email", $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":empresa", $empresa, $empresa === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":nif", $nif, $nif === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":direccion", $direccion, $direccion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":ciudad", $ciudad, $ciudad === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":codigo_postal", $codigo_postal, $codigo_postal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":pais", $pais, $pais === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($stmt->execute()) {
            $codi_resposta = 1;

            // refrescar valores mostrados (para ver cambios al instante)
            $nom_old = $nombre;
            $telefon_old = $telefono;
            $anualitat_old = $anualitat;
            $locale_old = $locale;
            $email_old = $email;
            $empresa_old = $empresa;
            $nif_old = $nif;
            $direccion_old = $direccion;
            $ciudad_old = $ciudad;
            $codigo_postal_old = $codigo_postal;
            $pais_old = $pais;

        } else {
            $codi_resposta = 2;
        }

        if ($codi_resposta == 1) {
            echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Modificació realitzada correctament.</strong></h4>';
            echo "Client anual modificat amb èxit.</div>";
        } else {
            echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
            echo 'Les dades no s\'han transmès correctament.</div>';
        }

    } else {
        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
        echo 'Controla que totes les dades obligatòries siguin correctes (Nom + Idioma).</div>';
    }
}

// 3) Formulario (siempre mostrarlo; si quieres ocultarlo al guardar, cambia esta condición)
echo '<form action="" method="post" id="update-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

// nombre
echo '<div class="col-md-6">';
echo '<label>Nom i cognoms client (*):</label>';
echo '<input type="text" class="form-control" id="nombre" name="nombre" required value="' . htmlspecialchars((string)$nom_old, ENT_QUOTES) . '">';
echo '</div>';

// locale
echo '<div class="col-md-3">';
echo '<label>Idioma (*):</label>';
echo '<select class="form-control" id="locale" name="locale" required>';

$locales = [
    'ca' => 'Català',
    'es' => 'Español',
    'fr' => 'Français',
    'en' => 'English',
    'it' => 'Italiano'
];
foreach ($locales as $code => $label) {
    $selected = ($locale_old === $code) ? 'selected' : '';
    echo "<option value=\"$code\" $selected>$label</option>";
}
echo '</select>';
echo '</div>';

// telefono
echo '<div class="col-md-3">';
echo '<label>Telèfon:</label>';
echo '<input type="text" class="form-control" id="telefono" name="telefono" value="' . htmlspecialchars((string)($telefon_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// anualitat
echo '<div class="col-md-3">';
echo '<label>Anualitat:</label>';
echo '<input type="text" class="form-control" id="anualitat" name="anualitat" value="' . htmlspecialchars((string)($anualitat_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// email
echo '<div class="col-md-4">';
echo '<label>Email:</label>';
echo '<input type="email" class="form-control" id="email" name="email" value="' . htmlspecialchars((string)($email_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// empresa
echo '<div class="col-md-4">';
echo '<label>Empresa:</label>';
echo '<input type="text" class="form-control" id="empresa" name="empresa" value="' . htmlspecialchars((string)($empresa_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// nif
echo '<div class="col-md-4">';
echo '<label>NIF:</label>';
echo '<input type="text" class="form-control" id="nif" name="nif" value="' . htmlspecialchars((string)($nif_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// direccion
echo '<div class="col-md-8">';
echo '<label>Direcció:</label>';
echo '<input type="text" class="form-control" id="direccion" name="direccion" value="' . htmlspecialchars((string)($direccion_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// ciudad
echo '<div class="col-md-4">';
echo '<label>Ciutat:</label>';
echo '<input type="text" class="form-control" id="ciudad" name="ciudad" value="' . htmlspecialchars((string)($ciudad_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// codigo_postal
echo '<div class="col-md-4">';
echo '<label>Codi postal:</label>';
echo '<input type="text" class="form-control" id="codigo_postal" name="codigo_postal" value="' . htmlspecialchars((string)($codigo_postal_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

// pais
echo '<div class="col-md-4">';
echo '<label>País:</label>';
echo '<input type="text" class="form-control" id="pais" name="pais" value="' . htmlspecialchars((string)($pais_old ?? ''), ENT_QUOTES) . '">';
echo '</div>';

echo "<div class='col-12'>";
echo "<button id='update-client' name='update-client' type='submit' class='btn btn-primary'>Modifica client</button> ";
echo '<a href="' . APP_WEB . '/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
echo "</div>";

echo "</form>";

echo '</div>';
echo "</div>";
