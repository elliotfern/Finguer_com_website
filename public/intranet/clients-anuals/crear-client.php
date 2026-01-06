<?php
global $conn;
require_once APP_ROOT . '/public/intranet/inc/header.php';
require_once(APP_ROOT . '/public/intranet/inc/header-reserves-anuals.php');

echo "<div class='container'>";
echo "<h3>Alta nou client Abonament anual</h3>";

$codi_resposta = 2;

if (isset($_POST["alta-client"])) {

    // --- Obligatorio ---
    if (empty($_POST["nombre"])) {
        $hasError = true;
    } else {
        $nombre = data_input($_POST["nombre"], ENT_NOQUOTES);
    }

    // --- Locale obligatorio ---
    $localesPermitidos = ['ca', 'es', 'fr', 'en', 'it'];

    if (empty($_POST["locale"]) || !in_array($_POST["locale"], $localesPermitidos, true)) {
        $hasError = true;
    } else {
        $locale = $_POST["locale"];
    }


    // --- Opcionales ---
    $telefono = empty($_POST["telefono"]) ? null : data_input($_POST["telefono"], ENT_NOQUOTES);
    $anualitat = empty($_POST["anualitat"]) ? null : data_input($_POST["anualitat"], ENT_NOQUOTES);

    // Campos nuevos opcionales
    $email         = empty($_POST["email"]) ? null : data_input($_POST["email"], ENT_NOQUOTES);
    $empresa       = empty($_POST["empresa"]) ? null : data_input($_POST["empresa"], ENT_NOQUOTES);
    $nif           = empty($_POST["nif"]) ? null : data_input($_POST["nif"], ENT_NOQUOTES);
    $direccion     = empty($_POST["direccion"]) ? null : data_input($_POST["direccion"], ENT_NOQUOTES);
    $ciudad        = empty($_POST["ciudad"]) ? null : data_input($_POST["ciudad"], ENT_NOQUOTES);
    $codigo_postal = empty($_POST["codigo_postal"]) ? null : data_input($_POST["codigo_postal"], ENT_NOQUOTES);
    $pais          = empty($_POST["pais"]) ? null : data_input($_POST["pais"], ENT_NOQUOTES);

    $tipoUsuario = 'cliente_anual';

    // Si no hi ha cap error, envia el formulari
    if (!isset($hasError)) {

        $sql = "INSERT INTO usuarios SET
            nombre=:nombre,
            telefono=:telefono,
            anualitat=:anualitat,
            tipo_rol=:tipo_rol,
            email=:email,
            empresa=:empresa,
            nif=:nif,
            direccion=:direccion,
            ciudad=:ciudad,
            codigo_postal=:codigo_postal,
            pais=:pais,
            locale=:locale
        ";

        $stmt = $conn->prepare($sql);

        $stmt->bindValue(":nombre", $nombre, PDO::PARAM_STR);

        // si son NULL, pasamos PDO::PARAM_NULL
        $stmt->bindValue(":telefono", $telefono, $telefono === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":anualitat", $anualitat, $anualitat === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        $stmt->bindValue(":tipo_rol", $tipoUsuario, PDO::PARAM_STR);
        $stmt->bindValue(":locale", $locale, PDO::PARAM_STR);

        $stmt->bindValue(":email", $email, $email === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":empresa", $empresa, $empresa === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":nif", $nif, $nif === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":direccion", $direccion, $direccion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":ciudad", $ciudad, $ciudad === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":codigo_postal", $codigo_postal, $codigo_postal === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $stmt->bindValue(":pais", $pais, $pais === null ? PDO::PARAM_NULL : PDO::PARAM_STR);

        if ($stmt->execute()) {
            $codi_resposta = 1;
        } else {
            $codi_resposta = 2;
        }

        if ($codi_resposta == 1) {
            echo '<div class="alert alert-success" role="alert"><h4 class="alert-heading"><strong>Alta realizada correctament.</strong></h4>';
            echo "Alta client anual amb èxit.</div>";
        } else {
            echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error en la transmissió de les dades</strong></h4>';
            echo 'Les dades no s\'han transmès correctament.</div>';
        }

    } else {
        // Error > bloqueja i mostra avis
        echo '<div class="alert alert-danger" role="alert"><h4 class="alert-heading"><strong>Error!</strong></h4>';
        echo 'Controla que totes les dades siguin correctes.</div>';
    }
}

if ($codi_resposta == 2) {
    echo '<form action="" method="post" id="alta-client" class="row g-3" style="background-color:#BDBDBD;padding:25px;margin-top:10px">';

    echo '<div class="col-md-4">';
    echo '<label>Nom i cognoms client (obligatori):</label>';
    echo '<input type="text" class="form-control" id="nombre" name="nombre" value="' . htmlspecialchars($_POST["nombre"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Telèfon client (obligatori):</label>';
    echo '<input type="text" class="form-control" id="telefono" name="telefono" value="' . htmlspecialchars($_POST["telefono"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-6">';
    echo '<label>Anualitat client (obligatori):</label>';
    echo '<input type="text" class="form-control" id="anualitat" name="anualitat" value="' . htmlspecialchars($_POST["anualitat"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Idioma (obligatori):</label>';
    echo '<select class="form-control" id="locale" name="locale" required>';

    $locales = [
        'ca' => 'Català',
        'es' => 'Español',
        'fr' => 'Français',
        'en' => 'English',
        'it' => 'Italiano'
    ];

    $localeActual = $_POST["locale"] ?? 'es';

    foreach ($locales as $code => $label) {
        $selected = ($localeActual === $code) ? 'selected' : '';
        echo "<option value=\"$code\" $selected>$label</option>";
    }

    echo '</select>';
    echo '</div>';

    echo '<hr>';

    // --- Nuevos inputs (opcionales) ---
    echo '<div class="col-md-6">';
    echo '<label>Email:</label>';
    echo '<input type="email" class="form-control" id="email" name="email" value="' . htmlspecialchars($_POST["email"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-6">';
    echo '<label>Empresa:</label>';
    echo '<input type="text" class="form-control" id="empresa" name="empresa" value="' . htmlspecialchars($_POST["empresa"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>NIF:</label>';
    echo '<input type="text" class="form-control" id="nif" name="nif" value="' . htmlspecialchars($_POST["nif"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-8">';
    echo '<label>Direcció:</label>';
    echo '<input type="text" class="form-control" id="direccion" name="direccion" value="' . htmlspecialchars($_POST["direccion"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Ciutat:</label>';
    echo '<input type="text" class="form-control" id="ciudad" name="ciudad" value="' . htmlspecialchars($_POST["ciudad"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>Codi postal:</label>';
    echo '<input type="text" class="form-control" id="codigo_postal" name="codigo_postal" value="' . htmlspecialchars($_POST["codigo_postal"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo '<div class="col-md-4">';
    echo '<label>País:</label>';
    echo '<input type="text" class="form-control" id="pais" name="pais" value="' . htmlspecialchars($_POST["pais"] ?? "", ENT_QUOTES) . '">';
    echo '</div>';

    echo "<div class='md-12'>";
    echo "<button id='alta-client' name='alta-client' type='submit' class='btn btn-primary'>Alta client</button>
          <a href='" . APP_WEB . "/clients-anuals/crear/client/'></a>
          </div>";

    echo "</form>";
} else {
    echo '<a href="' . APP_WEB . '/clients-anuals/" class="btn btn-dark menuBtn" role="button" aria-disabled="false">Tornar</a>';
}

echo '</div>
                </div>';

echo "</div>";
