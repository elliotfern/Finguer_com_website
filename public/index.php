<?php
// Configuración inicial para mostrar errores en desarrollo
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/backend/bootstrap.php';

function isApiRequest(): bool
{
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    return str_starts_with($uri, '/api/') ||
        str_contains($accept, 'application/json');
}

function denyAccess(int $code = 403, string $message = 'Accés denegat'): never
{
    http_response_code($code);

    if (isApiRequest()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(
            [
                'status' => 'error',
                'message' => $message,
            ],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        exit();
    }

    // HTML intranet
    // Opción A: mostrar mensaje simple
    echo "<h1>{$code}</h1><p>" .
        htmlspecialchars($message, ENT_QUOTES, 'UTF-8') .
        '</p>';
    exit();

    // Opción B: redirigir a una pantalla:
    // header('Location: /control/sense-permisos', true, 302);
    // exit;
}

// Obtener la ruta solicitada
$requestUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Normalizar la ruta eliminando barras finales, excepto para la raíz
$requestUri = rtrim($requestUri, '/');
if ($requestUri === '') {
    $requestUri = '/';
}

// Detectar el idioma desde la URL (si existe en la ruta)
preg_match('#^/(fr|en|ca)(/|$)#', $requestUri, $matches);
$language = $matches[1] ?? null; // Si hay un idioma en la URL, lo usamos

// Si no hay idioma en la URL y es la raíz (o idioma por defecto), usamos 'es'
if (empty($language)) {
    // Comprobamos si la ruta es la raíz (ejemplo: /reserva) y no incluye idioma
    if (preg_match('#^/$#', $requestUri)) {
        $language = 'es'; // Asumimos que si está en la raíz, el idioma es 'es'
    } else {
        // Si la cookie 'language' ya existe, usamos ese valor; sino, asignamos 'es' por defecto
        $language = $_COOKIE['language'] ?? 'es';
    }
}

// Establecer la cookie del idioma
setcookie('language', $language, time() + 3600 * 24 * 30, '/'); // 30 días
$_COOKIE['language'] = $language;

// Cargar las traducciones correspondientes al idioma
$translations = require __DIR__ . "../../src/backend/locales/{$language}.php";

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
        $routeParams = array_slice($matches, 1); // El primer elemento es la ruta misma, los parámetros son los siguientes

        // Asignamos la vista asociada a la ruta
        $view = $routeInfo['view'];
        break;
    }
}

// Si la ruta no es encontrada, asignamos la página 404
if (!$routeFound) {
    $view = '404.php';
    $noHeaderFooter = false;
} else {
    // Verificar si la ruta requiere sesión
    // AREA INTRANET TRABAJADORES
    $needsSession = $routeInfo['needs_session'] ?? false;
    if ($needsSession) {
        verificarSesion(); // Llamada a la función de verificación de sesión
    }

    // ✅ Autorización por roles (opcional por ruta)
    $roles = $routeInfo['roles'] ?? null;
    if (is_array($roles) && !empty($roles)) {
        // Ya hay sesión (verificarSesion), así que auth_user() debería existir
        if (!auth_has_role($roles)) {
            denyAccess(403, 'No tens permisos per accedir a aquesta secció.');
        }
    }

    // ✅ Atajo si prefieres flag needs_admin (opcional)
    $needsAdmin = $routeInfo['needs_admin'] ?? false;
    if ($needsAdmin) {
        if (!auth_is_admin()) {
            denyAccess(403, 'No tens permisos (admin requerit).');
        }
    }

    // Verificar si la ruta necesita verificación adicional
    // AREA CLIENTE
    $needsVerification = $routeInfo['needs_verification'] ?? false;
    if ($needsVerification) {
        verificarAcceso(); // Llamada al middleware para verificar la verificación
    }

    // Determinar si la vista necesita encabezado y pie de página
    $noHeaderFooter = $routeInfo['no_header_footer'] ?? false;
}

// Incluir encabezado y pie de página si no se especifica que no lo tenga
if (!$noHeaderFooter) {
    include 'includes/header.php';
}

// Incluir la vista asociada a la ruta
include $view;

// Incluir pie de página si no se especifica que no lo tenga
if (!$noHeaderFooter) {
    include 'includes/footer.php';
}
