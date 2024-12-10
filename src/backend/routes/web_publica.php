<?php

$languages = ['es', 'fr', 'en', 'ca']; // Idiomas soportados
$default_language = 'es'; // Idioma por defecto

// Función para generar rutas específicas por idioma
function generateLanguageRoutes(array $languages, string $default_language): array
{
    $routes = [];

    // Define las rutas base que quieres traducir
    $base_routes = [
        '/' => 'public/web-publica/index.php',
        '/reserva' => 'public/web-publica/index.php',
        '/pago' => 'public/web-publica/pagina_pago.php',
        '/compra-realizada' => 'public/web-publica/pagina_exito.php',
        '/error-compra' => 'public/web-publica/pagina_error.php',
        '/politica-de-privacidad-finguer' => 'public/web-publica/politica-privacidad.php',
        '/terminos-y-condiciones' => 'public/web-publica/terminos-condiciones.php',
    ];

    // Genera las rutas para cada idioma
    foreach ($languages as $lang) {
        foreach ($base_routes as $path => $view) {
            // Se crean las rutas con el prefijo de idioma (por ejemplo, /fr/, /en/, /ca/)
            if ($lang === $default_language) {
                // La ruta raíz para el idioma por defecto se mantiene como está
                $routes[$path] = [
                    'view' => $view,
                    'needs_session' => false,
                ];
            } else {
                // Las rutas para otros idiomas tendrán el prefijo de idioma (ej. /fr/, /en/)
                $routes["/{$lang}{$path}"] = [
                    'view' => $view,
                    'needs_session' => false,
                ];
            }
        }
    }

    return $routes;
}

// Rutas principales sin idioma explícito (solo para el idioma por defecto)
$routes = [
    '/' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],

    '/reserva' => ['view' => 'public/web-publica/index.php', 'needs_session' => false],

    '/pago' => ['view' => 'public/web-publica/pagina_pago.php', 'needs_session' => false],

    '/compra-realizada' => ['view' => 'public/web-publica/pagina_exito.php', 'needs_session' => false],

    '/error-compra' => ['view' => 'public/web-publica/pagina_error.php', 'needs_session' => false],

    '/politica-de-privacidad-finguer' => ['view' => 'public/web-publica/politica-privacidad.php', 'needs_session' => false],

    '/terminos-y-condiciones' => ['view' => 'public/web-publica/terminos-condiciones.php', 'needs_session' => false],
];

// Unir rutas base con rutas específicas de idioma
$routes = $routes + generateLanguageRoutes($languages, $default_language);
return $routes;
