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

$allowedEstados = ['pendiente_entrada', 'dentro', 'salido'];

if ($id <= 0) {
    jsonResponse(vp2_err('Parámetro id inválido', 'BAD_ID'), 400);
}
if (!in_array($nuevoEstado, $allowedEstados, true)) {
    jsonResponse(vp2_err('Parámetro estado_vehiculo inválido', 'BAD_ESTADO', [
        'allowed' => $allowedEstados
    ]), 400);
}

try {
    // 1) Leer estado actual (para validar transición y diferenciar 404)
    $st = $conn->prepare("
        SELECT estado_vehiculo
        FROM epgylzqu_parking_finguer_v2.parking_reservas
        WHERE id = :id
        LIMIT 1
    ");
    $st->execute([':id' => $id]);
    $estadoActual = $st->fetchColumn();

    if ($estadoActual === false) {
        jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
    }

    $estadoActual = (string)$estadoActual;

    // 2) Si ya está en el mismo estado, devolvemos OK (idempotente)
    if ($estadoActual === $nuevoEstado) {
        jsonResponse(vp2_ok('Sin cambios (ya estaba en ese estado)', [
            'id' => $id,
            'estado_vehiculo' => $nuevoEstado,
            'previous_estado_vehiculo' => $estadoActual,
        ]), 200);
    }

    // 3) Validar transición permitida
    $transiciones = [
        'pendiente_entrada' => ['dentro'],
        'dentro'            => ['salido'],
        'salido'            => [], // no se mueve
    ];

    $permitidos = $transiciones[$estadoActual] ?? [];
    if (!in_array($nuevoEstado, $permitidos, true)) {
        jsonResponse(vp2_err('Transición de estado no permitida', 'INVALID_TRANSITION', [
            'id' => $id,
            'from' => $estadoActual,
            'to' => $nuevoEstado,
            'allowed_to' => $permitidos,
        ]), 409);
    }

    // 4) Update con control de concurrencia (anti doble-click / carreras)
    $sql = "
        UPDATE epgylzqu_parking_finguer_v2.parking_reservas
        SET estado_vehiculo = :nuevo
        WHERE id = :id
          AND estado_vehiculo = :esperado
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':nuevo'    => $nuevoEstado,
        ':id'       => $id,
        ':esperado' => $estadoActual,
    ]);

    if ($stmt->rowCount() !== 1) {
        // Alguien lo cambió entre el SELECT y el UPDATE
        $st2 = $conn->prepare("
            SELECT estado_vehiculo
            FROM epgylzqu_parking_finguer_v2.parking_reservas
            WHERE id = :id
            LIMIT 1
        ");
        $st2->execute([':id' => $id]);
        $estadoAhora = $st2->fetchColumn();
        $estadoAhora = $estadoAhora === false ? null : (string)$estadoAhora;

        jsonResponse(vp2_err('Conflicto: el estado ha cambiado, recarga la tabla', 'CONFLICT', [
            'id' => $id,
            'expected' => $estadoActual,
            'current' => $estadoAhora,
            'requested' => $nuevoEstado,
        ]), 409);
    }

    jsonResponse(vp2_ok('Estado actualizado correctamente', [
        'id' => $id,
        'estado_vehiculo' => $nuevoEstado,
        'previous_estado_vehiculo' => $estadoActual,
    ]), 200);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error al actualizar el estado de la reserva', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
