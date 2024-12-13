<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/cron/reserves' => 'src/backend/api/cron/cron-pagats.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // TREBALLS CRON RESERVES - SENSE SESSIO PRIVADA
    '/cron/reserves' => [
        'view' => 'src/backend/api/cron/cron-pagats.php',
        'needs_session' => false,
        'no_header_footer' => true,
    ],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, false);

return $routes;
