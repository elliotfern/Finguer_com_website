<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/' => 'public/web-publica/index.php',
    '/reserva' => 'public/web-publica/index.php',
    '/pago/{id}' => 'public/web-publica/pagina_pago.php',
    '/compra-realizada' => 'public/web-publica/pagina_exito.php',
    '/error-compra' => 'public/web-publica/pagina_error.php',
    '/politica-privacidad' => 'public/web-publica/politica-privacidad.php',
    '/politica-cookies' => 'public/web-publica/politica-cookies.php',
    '/terminos-y-condiciones' => 'public/web-publica/terminos-condiciones.php',
    '/aviso-legal' => 'public/web-publica/aviso-legal.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    '/' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],
    '/reserva' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],
    '/pago/{id}' => ['view' => 'public/web-publica/pagina_pago.php', 'needs_session' => false],
    '/compra-realizada' => ['view' => 'public/web-publica/pagina_exito.php', 'needs_session' => false],
    '/error-compra' => ['view' => 'public/web-publica/pagina_error.php', 'needs_session' => false],
    '/politica-privacidad' => ['view' => 'public/web-publica/politica-privacidad.php', 'needs_session' => false],
    '/politica-cookies' => ['view' => 'public/web-publica/politica-cookies.php', 'needs_session' => false],
    '/terminos-y-condiciones' => ['view' => 'public/web-publica/terminos-condiciones.php', 'needs_session' => false],
    '/aviso-legal' => ['view' => 'public/web-publica/aviso-legal.php', 'needs_session' => false],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, true);

return $routes;
