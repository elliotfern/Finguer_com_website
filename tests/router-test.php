<?php
// tests/router-test.php
// Router para el servidor embebido de PHP en CI, replica .htaccess reglas 2 y 5.

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/../public';
$file = $publicDir . $uri;

// Regla 2 del .htaccess: si es un archivo o directorio real, servirlo tal cual
if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    return false; // deja que el servidor embebido lo sirva directamente
}

// Regla 5 del .htaccess: todo lo demás va al front controller
chdir($publicDir);
require $publicDir . '/index.php';
