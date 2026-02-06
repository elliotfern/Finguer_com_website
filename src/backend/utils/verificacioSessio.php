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

/**
 * True si el usuario logueado es admin
 */
function auth_is_admin(): bool
{
    $u = auth_user();
    return $u !== null && $u['role'] === 'admin';
}

/**
 * True si el usuario logueado tiene uno de estos roles
 */
function auth_has_role(array $roles): bool
{
    $u = auth_user();
    if ($u === null) return false;
    return in_array($u['role'], $roles, true);
}

/**
 * Para futuro: autorización por "capabilities" (permisos finos).
 * Por ahora:
 *  - admin => true
 *  - trabajador => false (por defecto)
 *
 * Luego aquí meterás tu lógica (por pantalla/acción/canal/etc.)
 */
function auth_can(string $capability, array $context = []): bool
{
    $u = auth_user();
    if ($u === null) return false;

    if ($u['role'] === 'admin') return true;

    if ($u['role'] === 'trabajador') {
        return match ($capability) {
            'menu.admin'        => false,
            'reserva.update'    => in_array($context['campo'] ?? '', ['fecha', 'vehiculo']),
            'reserva.view'      => true,
            'factura.emitir'    => false,
            default             => false,
        };
    }

    return false;
}

function http_is_ajax_or_api(): bool {
    $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
    $xhr    = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
    $uri    = $_SERVER['REQUEST_URI'] ?? '';
    return str_contains($accept, 'application/json')
        || strtolower($xhr) === 'xmlhttprequest'
        || str_starts_with($uri, '/api/');
}

function respond_json(int $code, array $payload): never {
    http_response_code($code);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function deny(int $code, string $message = 'Accés denegat'): never {
    if (http_is_ajax_or_api()) {
        respond_json($code, [
            'status' => 'error',
            'message' => $message,
        ]);
    }

    // HTML (intranet)
    http_response_code($code);

    // opción A: render simple
    echo "<h1>{$code}</h1><p>" . htmlspecialchars($message, ENT_QUOTES, 'UTF-8') . "</p>";
    exit;

    // opción B: redirigir a una pantalla:
    // header('Location: /control/sense-permisos/');
    // exit;
}

/** Requiere login */
function require_auth(): array {
    $u = auth_user();
    if ($u === null) deny(401, 'Has d’iniciar sessió');
    return $u;
}

/** Requiere admin */
function require_admin(): array {
    $u = require_auth();
    if (($u['role'] ?? null) !== 'admin') deny(403, 'No tens permisos (admin requerit)');
    return $u;
}

/** Requiere uno de estos roles */
function require_role(array $roles): array {
    $u = require_auth();
    if (!in_array($u['role'] ?? null, $roles, true)) deny(403, 'No tens permisos');
    return $u;
}

/** Requiere capability (tu auth_can) */
function require_can(string $capability, array $context = []): array {
    $u = require_auth();
    if (!auth_can($capability, $context)) deny(403, 'No tens permisos');
    return $u;
}
