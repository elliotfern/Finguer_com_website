<?php

// Determinar el esquema (http/https) de forma robusta:
// - REQUEST_SCHEME lo rellena Apache, pero no el servidor embebido de PHP (usado en tests de integración HTTP)
// - Fallback: comprobar HTTPS, o asumir 'http' si no hay ninguna pista
$scheme =
    $_SERVER['REQUEST_SCHEME'] ??
    (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'
        ? 'https'
        : 'http');

// Definir constantes de configuración
define('BASE_URL', $scheme . '://' . $_SERVER['HTTP_HOST']);
define('APP_ROOT', $_SERVER['DOCUMENT_ROOT']);

$base_url = $scheme . '://' . $_SERVER['HTTP_HOST'];
define('APP_WEB', $base_url);
define('APP_SERVER', $base_url);
