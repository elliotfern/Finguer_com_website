<?php
// Configuración inicial para mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir configuraciones y rutas
require_once __DIR__ . '/src/backend/config/config.php';
require_once __DIR__ . '/src/backend/config/funcions.php';
require_once __DIR__ . '/src/backend/routes/routes.php';

// Obtener la ruta solicitada
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar la ruta eliminando barras finales, excepto para la raíz
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '') {
    $requestUri = '/';
}

// Verificar si la ruta es solo el idioma, sin "/reserva"
if (preg_match('#^/(fr|en|ca)$#', $requestUri, $matches)) {
    $language = $matches[1];
    // Redirigir a la página correspondiente /fr/reserva, /en/reserva, /ca/reserva
    header("Location: /$language/reserva", true, 301);
    exit();
}

// Detectar el idioma desde la URL (si existe en la ruta)
preg_match('#^/(fr|en|ca)/#', $requestUri, $matches);
$language = $matches[1] ?? null;  // Si hay un idioma en la URL, lo usamos

// Si no hay idioma en la URL y es la raíz (o idioma por defecto), usamos 'es'
if (empty($language)) {
    // Comprobamos si la ruta es la raíz (ejemplo: /reserva) y no incluye idioma
    if (preg_match('#^/reserva$#', $requestUri)) {
        $language = 'es';  // Asumimos que si está en la raíz, el idioma es 'es'
    } else {
        // Si la cookie 'language' ya existe, usamos ese valor; sino, asignamos 'es' por defecto
        $language = $_COOKIE['language'] ?? 'es';
    }
}

// Establecer la cookie del idioma
setcookie('language', $language, time() + 3600 * 24 * 30, '/');  // 30 días
$_COOKIE['language'] = $language;


// Cargar las traducciones correspondientes al idioma
$translations = require __DIR__ . "/src/backend/locales/{$language}.php";

// Inicializar una variable para los parámetros de la ruta
$routeParams = [];

// Buscar si la ruta es una ruta dinámica y extraer los parámetros
$routeFound = false;
foreach ($routes as $route => $routeInfo) {
    // Crear un patrón para la ruta dinámica reemplazando los parámetros {param} por expresiones regulares
    $pattern = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_-]+)', $route);

    if (preg_match('#^' . $pattern . '$#', $requestUri, $matches)) {
        // Si encontramos la ruta, extraemos los parámetros
        $routeFound = true;
        $routeParams = array_slice($matches, 1);  // El primer elemento es la ruta misma, los parámetros son los siguientes

        // Asignamos la vista asociada a la ruta
        $view = $routeInfo['view'];
        break;
    }
}

// Si la ruta no es encontrada, asignamos la página 404
if (!$routeFound) {
    $view = 'public/404.php';
    $noHeaderFooter = false;
} else {
    // Verificar si la ruta requiere sesión
    $needsSession = $routeInfo['needs_session'] ?? false;
    if ($needsSession) {
        verificarSesion(); // Llamada a la función de verificación de sesión
    }

    // Verificar si la ruta necesita verificación adicional
    $needsVerification = $routeInfo['needs_verification'] ?? false;
    if ($needsVerification) {
        verificarAcceso(); // Llamada al middleware para verificar la verificación
    }

    // Determinar si la vista necesita encabezado y pie de página
    $noHeaderFooter = $routeInfo['no_header_footer'] ?? false;
}

// Incluir encabezado y pie de página si no se especifica que no lo tenga
if (!$noHeaderFooter) {
    include 'public/includes/header.php';
}

// Incluir la vista asociada a la ruta
include $view;

// Incluir pie de página si no se especifica que no lo tenga
if (!$noHeaderFooter) {
    include 'public/includes/footer.php';
}
