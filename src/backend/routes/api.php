<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/api/area-client/login' => 'src/backend/api/area-client/authClient.php',
    '/api/area-client/reservas' => 'src/backend/api/area-client/reservas.php',

    // API INTRANET
    '/api/intranet/auth/login' => 'src/backend/api/intranet/auth/login.php',
    '/api/intranet/reserves/get' => 'src/backend/api/intranet/get-reserves.php',
    '/api/intranet/reserves/post' => 'src/backend/api/intranet/post-reserves.php',
    '/api/intranet/users/get' => 'src/backend/api/intranet/get-users.php',
    '/api/intranet/email/get' => 'src/backend/api/intranet/email/get-email.php',

    // API WEB PUBLICA
    '/api/carro-compra' => 'src/backend/api/web_publica/carro-compra.php',
    '/api/carro-compra-session' => 'src/backend/api/web_publica/carro-compra-session.php',
    '/api/alta-client' => 'src/backend/api/web_publica/crear-usuario.php',
    '/api/alta-reserva' => 'src/backend/api/web_publica/crear-reserva.php',
    '/api/pagamentRedsysTargeta' => 'src/backend/api/web_publica/pagament-redsys-targeta.php',
    '/api/pagamentRedsysBizum' => 'src/backend/api/web_publica/pagament-redsys-bizum.php',

    // API FACTURES
    '/api/factures/pdf' => 'src/backend/api/intranet/factures/generar-factura.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    '/api/area-client/login' => ['view' => 'src/backend/api/area-client/authClient.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/area-client/reservas' => ['view' => 'src/backend/api/area-client/reservas.php', 'needs_session' => false, 'no_header_footer' => true],

    // API INTRANET
    '/api/intranet/auth/login' => ['view' => 'src/backend/api/intranet/auth/login.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/intranet/reserves/get' => ['view' => 'src/backend/api/intranet/get-reserves.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/reserves/post' => ['view' => 'src/backend/api/intranet/post-reserves.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/users/get' => ['view' => 'src/backend/api/intranet/get-users.php', 'needs_session' => true, 'no_header_footer' => true],

    '/api/intranet/email/get' => ['view' => 'src/backend/api/intranet/email/get-email.php', 'needs_session' => true, 'no_header_footer' => true],

    // API WEB PUBLICA

    '/api/carro-compra' => ['view' => 'src/backend/api/web_publica/carro-compra.php', 'needs_session' => false, 'no_header_footer' => true],
    '/api/carro-compra-session' => ['view' => 'src/backend/api/web_publica/carro-compra-session.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/alta-client' => ['view' => 'src/backend/api/web_publica/crear-usuario.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/alta-reserva' => ['view' => 'src/backend/api/web_publica/crear-reserva.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysTargeta' => ['view' => 'src/backend/api/web_publica/pagament-redsys-targeta.php', 'needs_session' => false, 'no_header_footer' => true],

    '/api/pagamentRedsysBizum' => ['view' => 'src/backend/api/web_publica/pagament-redsys-bizum.php', 'needs_session' => false, 'no_header_footer' => true],

    // API FACTURES
    '/api/factures/pdf' => ['view' => 'src/backend/api/intranet/factures/generar-factura.php', 'needs_session' => false, 'no_header_footer' => true],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, false);

return $routes;
