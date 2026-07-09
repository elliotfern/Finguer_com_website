<?php
// tests/router-test.php

$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$publicDir = __DIR__ . '/../public';
$file = $publicDir . $uri;

error_log("[router-test] URI recibida: {$uri}");
error_log(
    '[router-test] QUERY_STRING: ' . ($_SERVER['QUERY_STRING'] ?? '(vacío)'),
);

if ($uri !== '/' && (is_file($file) || is_dir($file))) {
    error_log("[router-test] Sirviendo archivo real: {$file}");
    return false;
}

error_log('[router-test] Delegando a index.php');
chdir($publicDir);
require $publicDir . '/index.php';
