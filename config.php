<?php

// Definir constantes de configuración
define('BASE_URL', $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST']);
define('APP_ROOT', __DIR__);

$base_url = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'];
define("APP_WEB",$base_url);
define("APP_SERVER",$base_url);

// Configurar cookies de sesión
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 30); // 30 días
ini_set('session.cookie_secure', true); // HTTPS
ini_set('session.cookie_httponly', true);
ini_set('session.cookie_samesite', 'Strict');

// Cargar librerías externas
require_once __DIR__ . '/vendor/autoload.php';
require_once 'public/apiRedsys.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();
require_once 'public/connection.php';