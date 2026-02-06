<?php

declare(strict_types=1);

requireMethod('POST');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

$type = (string)($_GET['type'] ?? '');
if ($type !== 'cancelar-reserva') {
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', ['allowed' => ['cancelar-reserva']]), 400);
}

/**
 * ✅ AJUSTA ESTO a tu sistema real:
 * Necesitamos el role del usuario autenticado.
 * Ejemplo:
 *   $me = authUser(); // ['id'=>..., 'role'=>'admin', ...]
 * Si no tienes authUser(), usa el helper que ya tengas (payload del JWT/cookie).
 */
$me = auth_user(); // <-- cambia si tu helper se llama distinto
$role = (string)($me['role'] ?? '');

if ($role !== 'admin') {
    jsonResponse(vp2_err('No autorizado', 'FORBIDDEN'), 403);
}

$input = readJsonBody(true);

// Acepta tanto "id" como "reserva_id" (para no romper front)
$id = 0;
if (isset($input['id'])) {
    $id = (int)$input['id'];
} elseif (isset($input['reserva_id'])) {
    $id = (int)$input['reserva_id'];
}

// Opcional: control concurrencia "optimista" desde cliente
$expected = isset($input['expected_estado']) ? (string)$input['expected_estado'] : null;

try {
    $result = cancelarReserva($conn, $id, $expected);

    $msg = ($result['changed'] ?? false)
        ? 'Reserva cancel·lada correctament'
        : 'Sin cambios (ya estaba cancelada)';

    jsonResponse(vp2_ok($msg, $result), 200);
} catch (InvalidArgumentException $e) {
    $code = $e->getMessage();

    if ($code === 'BAD_ID') {
        jsonResponse(vp2_err('Parámetro id inválido', 'BAD_ID'), 400);
    }

    if ($code === 'BAD_EXPECTED') {
        jsonResponse(vp2_err('Parámetro expected_estado inválido', 'BAD_EXPECTED', [
            'allowed' => estadosReservaAllowed(),
        ]), 400);
    }

    jsonResponse(vp2_err('Parámetros inválidos', 'BAD_PARAM'), 400);
} catch (ReservaNotFoundException $e) {
    jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
} catch (InvalidTransitionException $e) {
    // Por ejemplo: intentar cancelar cuando está "pagada"
    jsonResponse(vp2_err('Transición de estado no permitida', 'INVALID_TRANSITION', [
        'id' => $e->id,
        'from' => $e->from,
        'to' => $e->to,
        'allowed_to' => $e->allowedTo,
    ]), 409);
} catch (EstadoConflictException $e) {
    jsonResponse(vp2_err('Conflicto: el estado ha cambiado, recarga la tabla', 'CONFLICT', [
        'id' => $e->id,
        'expected' => $e->expected,
        'current' => $e->current,
        'requested' => $e->requested,
    ]), 409);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error al cancelar la reserva', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
