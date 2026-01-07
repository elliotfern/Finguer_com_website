<?php
declare(strict_types=1);

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function data_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
}

/** JWT válido => payload (stdClass). Si no => false */
function validarToken(string $jwt)
{
    $jwtSecret = $_ENV['TOKEN'];

    try {
        $decoded = JWT::decode($jwt, new Key($jwtSecret, 'HS256'));

        if (isset($decoded->exp) && (int)$decoded->exp < time()) return false;
        if (empty($decoded->sub) || empty($decoded->role)) return false;

        return $decoded;
    } catch (Throwable $e) {
        error_log('Error al validar el token: ' . $e->getMessage());
        return false;
    }
}

/**
 * Usuario autenticado desde token:
 * uuid (string), uuid_bin (16 bytes), role, name, jti
 */
function auth_user(): ?array
{
    if (empty($_COOKIE['token'])) return null;

    $payload = validarToken((string)$_COOKIE['token']);
    if ($payload === false) return null;

    $uuidStr = (string)$payload->sub;

    try {
        $uuidBin = uuid_bin_from_string($uuidStr); // tu helper del paso 1
    } catch (Throwable $e) {
        return null;
    }

    return [
        'uuid'     => $uuidStr,
        'uuid_bin' => $uuidBin,
        'role'     => (string)$payload->role,
        'name'     => (string)($payload->name ?? ''),
        'jti'      => (string)($payload->jti ?? ''),
    ];
}

/** Guard intranet: admin + trabajador */
function verificarSesionIntranet(): void
{
    $user = auth_user();
    if ($user === null) {
        header('Location: /control/login');
        exit();
    }

    $rolesPermitidos = ['admin', 'trabajador'];
    if (!in_array($user['role'], $rolesPermitidos, true)) {
        header('Location: /control/login');
        exit();
    }
}

/**
 * Guard área cliente.
 * Opción A (recomendada): rol explícito 'cliente'
 */
function verificarAccesoCliente(): void
{
    $user = auth_user();
    if ($user === null) {
        header('Location: /area-cliente/login');
        exit();
    }

    // ✅ AJUSTA este array a tu realidad (p.ej. 'cliente', 'usuario', 'client', etc.)
    $rolesPermitidos = ['cliente'];

    if (!in_array($user['role'], $rolesPermitidos, true)) {
        header('Location: /area-cliente/login');
        exit();
    }
}

/**
 * Compatibilidad con tu router actual:
 * - verificarSesion() se usa para needs_session (intranet)
 * - verificarAcceso() se usa para needs_verification (area cliente)
 */
function verificarSesion(): void
{
    verificarSesionIntranet();
}

function verificarAcceso(): void
{
    verificarAccesoCliente();
}
