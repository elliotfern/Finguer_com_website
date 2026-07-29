<?php

// Determinar el esquema (http/https) de forma robusta:
$scheme =
    $_SERVER['REQUEST_SCHEME'] ??
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        ? 'https'
        : 'http');

$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Definir constantes de configuración
define('BASE_URL', $scheme . '://' . $host);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT'] ?? '');

$base_url = $scheme . '://' . $host;

define('APP_WEB', $base_url);
define('APP_SERVER', $base_url);
