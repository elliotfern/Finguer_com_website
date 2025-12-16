<?php

declare(strict_types=1);

function jsonResponse(array $payload, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function vp2_ok(string $message, array $data = [], array $extra = []): array
{
    return array_merge([
        'status'  => 'success',
        'message' => $message,
        'data'    => $data,
    ], $extra);
}

function vp2_err(string $message, string $code = '', array $extra = []): array
{
    return array_merge([
        'status'  => 'error',
        'code'    => $code,
        'message' => $message,
    ], $extra);
}

function obtenerFacturaIdPorReserva(PDO $conn, int $reservaId): ?int
{
    $st = $conn->prepare("
        SELECT id
        FROM epgylzqu_parking_finguer_v2.facturas
        WHERE reserva_id = :rid
        ORDER BY id DESC
        LIMIT 1
    ");
    $st->execute([':rid' => $reservaId]);
    $id = $st->fetchColumn();
    return $id ? (int)$id : null;
}

function requireMethod(string $method): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== $method) {
        jsonResponse(vp2_err('Method not allowed', 'METHOD_NOT_ALLOWED'), 405);
    }
}

function requireAuthTokenCookie(): string
{
    $token = $_COOKIE['token'] ?? '';
    if ($token === '') {
        jsonResponse(vp2_err('Access not allowed (no token)', 'UNAUTHENTICATED'), 401);
    }
    if (!function_exists('validarToken') || !validarToken($token)) {
        jsonResponse(vp2_err('Invalid token', 'FORBIDDEN'), 403);
    }
    return $token;
}

function readJsonBody(bool $required = false): array
{
    $raw = file_get_contents('php://input');
    if (!$raw) {
        if ($required) jsonResponse(vp2_err('Missing JSON body', 'MISSING_BODY'), 400);
        return [];
    }
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        jsonResponse(vp2_err('Invalid JSON body', 'BAD_JSON'), 400);
    }
    return $data;
}

function getIntParam(string $key, bool $required = true): ?int
{
    $v = filter_input(INPUT_GET, $key, FILTER_VALIDATE_INT);
    if ($v === false || $v === null) {
        if ($required) jsonResponse(vp2_err("Parámetro {$key} inválido", 'BAD_PARAM'), 400);
        return null;
    }
    return (int)$v;
}

function getEnumParam(string $key, array $allowed, ?string $default = null): string
{
    $v = (string)($_GET[$key] ?? '');
    if ($v !== '' && in_array($v, $allowed, true)) return $v;
    if ($default !== null) return $default;
    return $allowed[0];
}
