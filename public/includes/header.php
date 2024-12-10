<?php
// Obtener el idioma actual desde la cookie
$currentLanguage = $_COOKIE['language'] ?? 'es';  // Si no está establecido, por defecto 'es'

// Idiomas disponibles
$languages = [
  'es' => 'Español',
  'fr' => 'Français',
  'en' => 'English',
  'ca' => 'Català',
];

// Obtener la URL actual sin el idioma (comenzando desde el primer segmento después de la raíz)
$currentUri = $_SERVER['REQUEST_URI'];

// Eliminar el idioma actual de la URL (por ejemplo, de '/es', '/fr', '/en', '/ca')
$baseUri = preg_replace('#^/(fr|en|ca)/#', '/', $currentUri);

$base_url = ($currentLanguage === 'es') ? '/' : "/$currentLanguage/";

// Si el idioma actual es español, no agregar el prefijo '/es'
if ($currentLanguage === 'es') {
  // Si el idioma es español, la URL debe ser simplemente '/reserva' o cualquier página sin el prefijo '/es'
  $baseUri = preg_replace('#^/es/#', '/', $currentUri);
}

// Obtener traducciones generales
$generalTranslations = $translations['header'] ?? [];
?>

<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Finguer - Parking Aeropuerto Barcelona El Prat T1 y T2</title>
  <meta name="description" content="Finguer es un parking para coches con servicio de traslado y recogida al aeropuerto de Barcelona. Pero nosotros nos consideramos más como un hotel para mascotas.">
  <meta name="keywords" content="Parking, Aeropuerto, El Prat, finguer, traslado">
  <link rel="icon" href="<?php APP_ROOT; ?>/public/img/favicon.png" type="image/png">
</head>

<body class="d-flex flex-column" style="height: 100vh; margin: 0;">

  <div class="container d-flex flex-column" style="flex: 1;">
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
        <a href="<?php echo APP_WEB . $base_url ?>"><img alt="Finguer" class="img-responsive" src="<?php APP_ROOT; ?>/public/img/logo-header.svg"></a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarSupportedContent">
          <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
              <a class="nav-link" aria-current="page" href="<?php echo APP_WEB . $base_url ?>reserva"><?php echo ($generalTranslations['home'] ?? 'Inicio') ?></a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="<?php echo APP_WEB . $base_url ?>reserva#servicios"><?php echo ($generalTranslations['servicios'] ?? 'Servicios') ?></a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="<?php echo APP_WEB . $base_url ?>reserva#donde-estamos"><?php echo ($generalTranslations['donde'] ?? 'Dónde estamos') ?></a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="<?php echo APP_WEB . $base_url ?>reserva#contacto"><?php echo ($generalTranslations['contacto'] ?? 'Contacto') ?></a>
            </li>

            <li class="nav-item">
              <a class="nav-link" href="<?php echo APP_WEB . $base_url ?>area-cliente/"><?php echo ($generalTranslations['micuenta'] ?? 'Mi cuenta') ?></a>
            </li>
          </ul>

        </div>
        <!-- Menú de idiomas con Bootstrap -->
        <div class="dropdown">
          <button class="btn btn-secondary dropdown-toggle" type="button" id="languageDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <?php echo $languages[$currentLanguage]; ?> <!-- Mostrar el idioma actual -->
          </button>
          <ul class="dropdown-menu" aria-labelledby="languageDropdown">
            <?php foreach ($languages as $langCode => $langName): ?>
              <li>
                <a class="dropdown-item" href="<?php echo $langCode === 'es' ? $baseUri : '/' . $langCode . $baseUri ?>"> <!-- Redirigir al mismo URI con el nuevo idioma -->
                  <?php echo $langName; ?>
                </a>
              </li>
            <?php endforeach; ?>
          </ul>
        </div>

      </div>
    </nav>
  </div>

  <div id="cookie-banner" class="cookie-banner" style="display: block;">
    <h4>¡Bienvenidos a Finguer!</h4>
    <p>Nosotros y nuestros socios almacenamos y/o accedemos a información en un dispositivo, como cookies, y procesamos datos personales, como identificadores únicos e información estándar enviada por un dispositivo para anuncios y contenido personalizados, medición de anuncios y contenido, e información sobre la audiencia, así como para desarrollar y mejorar productos. Con su permiso, nosotros y nuestros socios podemos utilizar datos de geolocalización precisos e identificación mediante el escaneo del dispositivo. Puede hacer clic para dar su consentimiento a nuestro procesamiento. Alternativamente, puede hacer clic para negarse a dar su consentimiento o acceder a información más detallada y cambiar sus preferencias antes de dar su consentimiento. Tenga en cuenta que es posible que algunos tratamientos de sus datos personales no requieran su consentimiento, pero usted tiene derecho a oponerse a dicho tratamiento. Sus preferencias se aplicarán únicamente a este sitio web. Puede cambiar sus preferencias en cualquier momento regresando a este sitio o visitando nuestra política de privacidad.</p>
    <div class="container">
      <div class="row">
        <div class="col">
          <button id="accept-cookies" class="btn btn-success">Aceptar cookies</button>
        </div>
        <div class="col">
          <button id="reject-cookies" class="btn btn-danger">Rechazar cookies</button>
        </div>
      </div>
    </div>
  </div>