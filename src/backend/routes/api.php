<?php

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [

    '/api/area-client/login' => ['view' => '../src/backend/api/area-client/authClient.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/area-client/reservas' => ['view' => '../src/backend/api/area-client/reservas.php', 'needs_session' => false, 'no_header_footer' => true],


    '/api/formulario/post' => ['view' => '../src/backend/api/web_publica/formulario/post-formulario.php', 'needs_session' => false, 'no_header_footer' => true],

    // API INTRANET
    '/api/intranet/auth/login' => ['view' => '../src/backend/api/intranet/auth/login.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/intranet/reserves/get' => ['view' => '../src/backend/api/intranet/get-reserves.php', 'needs_session' => true, 'no_header_footer' => true],
    '/api/intranet/reserves/post' => ['view' => '../src/backend/api/intranet/post-reserves.php', 'needs_session' => true, 'no_header_footer' => true],
    '/api/intranet/cancelar-reserva/post' => ['view' => '../src/backend/api/intranet/post-cancelar-reserves.php', 'needs_session' => true, 'no_header_footer' => true],


    '/api/intranet/users/get' => ['view' => '../src/backend/api/intranet/get-users.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/intranet/email/get' => ['view' => '../src/backend/api/intranet/email/get-email.php', 'needs_session' => true, 'no_header_footer' => true],

    // API WEB PUBLICA

    // 1 - CARRO DE LA COMPRA
    '/api/carro-compra/get' =>  ['view' => '../src/backend/api/web_publica/carro-compra/get-carro.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/carro-compra/post' => ['view' => '../src/backend/api/web_publica/carro-compra/post-carro.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/alta-client' => ['view' => '../src/backend/api/web_publica/crear-usuario.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/alta-reserva' => ['view' => '../src/backend/api/web_publica/crear-reserva.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysTargeta' => ['view' => '../src/backend/api/web_publica/pagament-redsys-targeta.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysBizum' => ['view' => '../src/backend/api/web_publica/pagament-redsys-bizum.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/notificacioRedsys' => ['view' => '../src/backend/api/web_publica/redsys-notificacio.php', 'needs_session' => false, 'no_header_footer' => true],

    // GESTIO CLIENTS I CLIENTS ANUALS
    '/api/clients/get/{slug}' => ['view' => '../src/backend/api/intranet/clients/get-clients.php', 'needs_session' => false, 'no_header_footer' => true],

    // API FACTURES
    '/api/factures/send' => ['view' => '../src/backend/api/intranet/factures/enviar-factura.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/factures/get' => ['view' => '../src/backend/api/intranet/factures/get-factura.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/factures/post' => ['view' => '../src/backend/api/intranet/factures/post-factura.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/factures/post/confirmar-pago-manual' => ['view' => '../src/backend/api/intranet/factures/post-confirmar-pago-manual.php', 'needs_session' => false, 'no_header_footer' => true],

    // /api/factures/logs' => ['view' => '../src/backend/api/intranet/factures/logs.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/factures/hash' => ['view' => '../src/backend/api/intranet/factures/hash.php', 'needs_session' => false, 'no_header_footer' => true],

     // API RESERVES
   '/api/reserves/post/{slug}' => [
        'view' => '../src/backend/api/intranet/reserves/post-reserves.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/reserves/put/{slug}' => [
        'view' => '../src/backend/api/intranet/reserves/put-reserves.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    // API USUARIS / CLIENTS
    '/api/usuaris/get' => [
        'view' => '../src/backend/api/intranet/usuaris/get-usuaris.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/usuaris/post' => [
        'view' => '../src/backend/api/intranet/usuaris/post-usuaris.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/usuaris/put' => [
        'view' => '../src/backend/api/intranet/usuaris/put-usuaris.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    // ALTRES
    '/api/uuid' => [
        'view' => '../src/backend/api/generarUUID.php',
        'no_header_footer' => true,
        'needs_session' => true,
    ],

    '/api/comprova' => [
        'view' => '../src/backend/api/comprova.php',
        'no_header_footer' => true,
        'needs_session' => false,
    ],

];


return $routes;
