<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finguer - Parking Aeropuerto Barcelona El Prat T1 y T2</title>
    <meta name="description" content="Finguer es un parking para coches con servicio de traslado y recogida al aeropuerto de Barcelona. Pero nosotros nos consideramos más como un hotel para mascotas.">
    <meta name="keywords" content="Parking, Aeropuerto, El Prat, finguer, traslado">
    <link rel="icon" href="<?php APP_ROOT;?>/public/img/favicon.png" type="image/png">

    <!-- Agrega los scripts de Stripe y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
     <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" 
    integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="<?php APP_ROOT;?>/public/style.css">

    <!-- Flatpickr CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <!-- Flatpickr JS -->
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <script type="module" src="<?php APP_ROOT;?>/dist/main.js"></script>
    <script src="<?php APP_ROOT;?>/public/js/cookies.js"></script>

</head>
<body class="d-flex flex-column" style="height: 100vh; margin: 0;">

<div class="container d-flex flex-column" style="flex: 1;"> 
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
  <a href="/"><img alt="Finguer" class="img-responsive" src="<?php APP_ROOT;?>/public/img/logo-header.svg"></a>

    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link" aria-current="page" href="<?php APP_ROOT;?>/">Inicio</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?php APP_ROOT;?>/#servicios">Servicios</a>
        </li>
       
        <li class="nav-item">
          <a class="nav-link" href="<?php APP_ROOT;?>/#donde-estamos">Dónde estamos</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?php APP_ROOT;?>/#contacto">Contacto</a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="<?php APP_ROOT;?>/area-cliente/">Mi cuenta</a>
        </li>
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