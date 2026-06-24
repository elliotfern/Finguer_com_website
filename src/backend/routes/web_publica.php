<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/' => ' ./web-publica/index.php',
    '/reserva' => './web-publica/index.php',
    '/pago/{id}' => './web-publica/pagina_pago.php',
    '/compra-realizada' => './web-publica/pagina_exito.php',
    '/error-compra' => './web-publica/pagina_error.php',
    '/politica-privacidad' => './web-publica/politica-privacidad.php',
    '/politica-cookies' => './web-publica/politica-cookies.php',
    '/terminos-y-condiciones' => './web-publica/terminos-condiciones.php',
    '/aviso-legal' => './web-publica/aviso-legal.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    '/' => ['view' => './web-publica/index.php', 'needs_session' => false],
    '/reserva' => [
        'view' => './web-publica/index.php',
        'needs_session' => false,
    ],
    '/pago/{id}' => [
        'view' => './web-publica/pagina_pago.php',
        'needs_session' => false,
    ],
    '/compra-realizada' => [
        'view' => './web-publica/pagina_exito.php',
        'needs_session' => false,
    ],
    '/error-compra' => [
        'view' => './web-publica/pagina_error.php',
        'needs_session' => false,
    ],
    '/politica-privacidad' => [
        'view' => './web-publica/politica-privacidad.php',
        'needs_session' => false,
    ],
    '/politica-cookies' => [
        'view' => './web-publica/politica-cookies.php',
        'needs_session' => false,
    ],
    '/terminos-y-condiciones' => [
        'view' => './web-publica/terminos-condiciones.php',
        'needs_session' => false,
    ],
    '/aviso-legal' => [
        'view' => './web-publica/aviso-legal.php',
        'needs_session' => false,
    ],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, true);

return $routes;
