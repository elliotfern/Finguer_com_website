<?php

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function data_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

// Función que verifica si el usuario tiene un token válido
function verificarSesion()
{
    // Inicia la sesión si no está ya iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica si la cookie del token existe y es válida
    if (!isset($_COOKIE['token']) || !validarToken($_COOKIE['token']) || !isset($_COOKIE['user_type']) || $_COOKIE['user_type'] != 1) {
        header('Location: /control/login'); // Redirige a login si no hay token válido
        exit();
    }
}

// Función que verifica si el usuario tiene acceso al area de cliente
function verificarAcceso()
{
    // Inicia la sesión si no está ya iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica si la cookie del token existe y es válida
    if (!isset($_COOKIE['user_id']) || $_COOKIE['acceso'] != "si") {
        header('Location: /area-cliente/login'); // Redirige a login si no hay token válido
        exit();
    }
}

function validarToken($jwt)
{

    $jwtSecret = $_ENV['TOKEN'];  // Tu clave secreta
    $decoded = null;

    try {

        $decoded = JWT::decode($jwt, new key($jwtSecret, 'HS256'));

        // Verifica si el token ha expirado
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return false;  // Token expirado
        }
    } catch (Exception $e) {
        // Manejo del error
        error_log('Error al validar el token: ' . $e->getMessage());  // Log del error para depuración
        return false;
    }

    // Si la decodificación es exitosa y el token es válido, se devuelve el payload
    return $decoded;
}
