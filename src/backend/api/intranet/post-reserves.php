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
if ($type !== 'update-estado') {
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', ['allowed' => ['update-estado']]), 400);
}

$input = readJsonBody(true);
$id = isset($input['id']) ? (int)$input['id'] : 0;
$nuevoEstado = isset($input['estado_vehiculo']) ? (string)$input['estado_vehiculo'] : '';

try {
    $result = cambiarEstadoReserva($conn, $id, $nuevoEstado);

    // changed true/false + previous_estado_vehiculo incluidos
    $msg = ($result['changed'] ?? false)
        ? 'Estado actualizado correctamente'
        : 'Sin cambios (ya estaba en ese estado)';

    jsonResponse(vp2_ok($msg, $result), 200);
} catch (InvalidArgumentException $e) {
    // CambiarEstadoReserva lanza BAD_ID o BAD_ESTADO como message
    $code = $e->getMessage();

    if ($code === 'BAD_ID') {
        jsonResponse(vp2_err('Parámetro id inválido', 'BAD_ID'), 400);
    }
    if ($code === 'BAD_ESTADO') {
        jsonResponse(vp2_err('Parámetro estado_vehiculo inválido', 'BAD_ESTADO', [
            'allowed' => estadosVehiculoAllowed(),
        ]), 400);
    }

    jsonResponse(vp2_err('Parámetros inválidos', 'BAD_PARAM'), 400);
} catch (ReservaNotFoundException $e) {
    jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
} catch (InvalidTransitionException $e) {
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
    jsonResponse(vp2_err('Error al actualizar el estado de la reserva', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
