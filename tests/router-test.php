<?php
// tests/router-test.php

// Puente explícito: algunas configuraciones de PHP no populan $_ENV
// automáticamente desde las variables de entorno del proceso.
foreach (['DB_HOST', 'DB_USER', 'DB_PASS', 'DB_DBNAME', 'TOKEN'] as $key) {
    $value = getenv($key);
    if ($value !== false && !isset($_ENV[$key])) {
        $_ENV[$key] = $value;
    }
}

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/../public';
$file = $publicDir . $uri;

if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    error_log("[router-test] Sirviendo archivo real: {$file}");
    return false;
}

chdir($publicDir);
require $publicDir . '/index.php';
