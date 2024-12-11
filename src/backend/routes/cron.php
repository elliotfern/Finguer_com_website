<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/cron/reserves' => 'public/cron/cron-reserves.php',
    '/cron/pagats' => 'public/cron/cron-pagats.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // TREBALLS CRON RESERVES - SENSE SESSIO PRIVADA
    '/cron/reserves' => ['view' => 'public/cron/cron-reserves.php', 'needs_session' => false],

    '/cron/pagats' => ['view' => 'public/cron/cron-pagats.php', 'needs_session' => false],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, false);

return $routes;
