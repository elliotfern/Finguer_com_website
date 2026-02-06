<?php

declare(strict_types=1);

// ✅ Ajusta si tienes otros estados
const ESTADOS_RESERVA_ALLOWED = ['pendiente', 'procesando_pago', 'pago_oficina', 'pagada', 'cancelada', 'anual'];

function estadosReservaAllowed(): array
{
    return ESTADOS_RESERVA_ALLOWED;
}

/**
 * Cancela una reserva cambiando parking_reservas.estado -> 'cancelada'
 * Reglas:
 * - id válido
 * - si ya está cancelada: idempotente
 * - NO permite cancelar si estado == 'pagada'
 * - control concurrencia: UPDATE ... WHERE estado = :esperado
 * - expected_estado opcional (si lo pasa cliente, se valida)
 */
function cancelarReserva(PDO $conn, int $id, ?string $expectedEstado = null): array
{
    if ($id <= 0) {
        throw new InvalidArgumentException('BAD_ID');
    }

    if ($expectedEstado !== null && !in_array($expectedEstado, ESTADOS_RESERVA_ALLOWED, true)) {
        throw new InvalidArgumentException('BAD_EXPECTED');
    }

    // 1) Estado actual
    $st = $conn->prepare("
        SELECT estado
        FROM parking_reservas
        WHERE id = :id
        LIMIT 1
    ");
    $st->execute([':id' => $id]);
    $estadoActual = $st->fetchColumn();

    if ($estadoActual === false) {
        throw new ReservaNotFoundException('NOT_FOUND');
    }

    $estadoActual = (string)$estadoActual;

    // 2) Si cliente mandó expected, úsalo como “esperado”; si no, usa el actual leído
    $estadoEsperado = $expectedEstado ?? $estadoActual;

    // Si expected no coincide con el actual, conflicto (opcional pero recomendable)
    if ($expectedEstado !== null && $expectedEstado !== $estadoActual) {
        throw new EstadoConflictException($id, $expectedEstado, $estadoActual, 'cancelada');
    }

    // 3) Idempotencia
    if ($estadoActual === 'cancelada') {
        return [
            'id' => $id,
            'estado' => 'cancelada',
            'previous_estado' => $estadoActual,
            'changed' => false,
        ];
    }

    // 4) Transiciones permitidas (regla de negocio)
    // Cancelar permitido cuando NO está pagada
    if ($estadoActual === 'pagada') {
        throw new InvalidTransitionException($id, $estadoActual, 'cancelada', []); // allowed_to vacío
    }

    // (Opcional) si quieres impedir cancelar "anual", descomenta:
    // if ($estadoActual === 'anual') {
    //     throw new InvalidTransitionException($id, $estadoActual, 'cancelada', []);
    // }

    // 5) Update con control concurrencia
    $stmt = $conn->prepare("
        UPDATE parking_reservas
        SET estado = 'cancelada'
        WHERE id = :id
          AND estado = :esperado
    ");
    $stmt->execute([
        ':id' => $id,
        ':esperado' => $estadoEsperado,
    ]);

    if ($stmt->rowCount() !== 1) {
        $st2 = $conn->prepare("
            SELECT estado
            FROM parking_reservas
            WHERE id = :id
            LIMIT 1
        ");
        $st2->execute([':id' => $id]);
        $estadoAhora = $st2->fetchColumn();
        $estadoAhora = $estadoAhora === false ? null : (string)$estadoAhora;

        throw new EstadoConflictException($id, $estadoEsperado, $estadoAhora, 'cancelada');
    }

    return [
        'id' => $id,
        'estado' => 'cancelada',
        'previous_estado' => $estadoActual,
        'changed' => true,
    ];
}
