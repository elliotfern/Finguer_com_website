<?php

declare(strict_types=1);

// ✅ Fuente de verdad única
const ESTADOS_VEHICULO_ALLOWED = ['pendiente_entrada', 'dentro', 'salido'];

/**
 * Getter para reutilizar desde endpoints (evita duplicar arrays).
 */
function estadosVehiculoAllowed(): array
{
    return ESTADOS_VEHICULO_ALLOWED;
}

function cambiarEstadoReserva(PDO $conn, int $id, string $nuevoEstado): array
{
    if ($id <= 0) {
        throw new InvalidArgumentException('BAD_ID');
    }
    if (!in_array($nuevoEstado, ESTADOS_VEHICULO_ALLOWED, true)) {
        throw new InvalidArgumentException('BAD_ESTADO');
    }

    // 1) Estado actual
    $st = $conn->prepare("
        SELECT estado_vehiculo
        FROM epgylzqu_parking_finguer_v2.parking_reservas
        WHERE id = :id
        LIMIT 1
    ");
    $st->execute([':id' => $id]);
    $estadoActual = $st->fetchColumn();

    if ($estadoActual === false) {
        throw new ReservaNotFoundException('NOT_FOUND');
    }
    $estadoActual = (string)$estadoActual;

    // 2) Idempotencia
    if ($estadoActual === $nuevoEstado) {
        return [
            'id' => $id,
            'estado_vehiculo' => $nuevoEstado,
            'previous_estado_vehiculo' => $estadoActual,
            'changed' => false,
        ];
    }

    // 3) Transiciones
    $transiciones = [
        'pendiente_entrada' => ['dentro'],
        'dentro'            => ['salido'],
        'salido'            => [],
    ];

    $permitidos = $transiciones[$estadoActual] ?? [];
    if (!in_array($nuevoEstado, $permitidos, true)) {
        throw new InvalidTransitionException($id, $estadoActual, $nuevoEstado, $permitidos);
    }

    // 4) Update con control concurrencia
    $stmt = $conn->prepare("
        UPDATE epgylzqu_parking_finguer_v2.parking_reservas
        SET estado_vehiculo = :nuevo
        WHERE id = :id
          AND estado_vehiculo = :esperado
    ");
    $stmt->execute([
        ':nuevo'    => $nuevoEstado,
        ':id'       => $id,
        ':esperado' => $estadoActual,
    ]);

    if ($stmt->rowCount() !== 1) {
        $st2 = $conn->prepare("
            SELECT estado_vehiculo
            FROM epgylzqu_parking_finguer_v2.parking_reservas
            WHERE id = :id
            LIMIT 1
        ");
        $st2->execute([':id' => $id]);
        $estadoAhora = $st2->fetchColumn();
        $estadoAhora = $estadoAhora === false ? null : (string)$estadoAhora;

        throw new EstadoConflictException($id, $estadoActual, $estadoAhora, $nuevoEstado);
    }

    return [
        'id' => $id,
        'estado_vehiculo' => $nuevoEstado,
        'previous_estado_vehiculo' => $estadoActual,
        'changed' => true,
    ];
}
