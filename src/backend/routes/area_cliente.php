<?php

// Define las rutas base que quieres traducir
$base_routes = [
    '/area-cliente/login' => 'public/area-client/login.php',
    '/area-cliente' => 'public/area-client/index.php',
    '/area-cliente/validar-token' => 'public/area-client/validar-token.php',
    '/area-cliente/reservas' => 'public/area-client/index.php',
];

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    // 05. Area cliente - necesita verificacion
    '/area-cliente/login' => ['view' => 'public/area-client/login.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => false],

    '/area-cliente' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],

    '/area-cliente/validar-token' => ['view' => 'public/area-client/validar-token.php', 'needs_session' => false, 'needs_verification' => false, 'no_header_footer' => true],

    '/area-cliente/reservas' => ['view' => 'public/area-client/index.php', 'needs_session' => false, 'needs_verification' => true, 'no_header_footer' => false],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($base_routes, true);

return $routes;
