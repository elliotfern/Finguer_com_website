<?php

function lecturaReserva(PDO $conn, int $reservaId): array
{
    if ($reservaId <= 0) {
        return vp2_err('ID de reserva no válido.', 'RESERVA_ID_INVALID');
    }

    $sql = "
        SELECT
            pr.id,
            pr.localizador,
            pr.usuario_id,
            pr.estado,
            pr.canal,
            pr.fecha_reserva
        FROM epgylzqu_parking_finguer_v2.parking_reservas pr
        WHERE pr.id = :id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $reservaId, PDO::PARAM_INT);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        return vp2_err('No se encontró la reserva.', 'RESERVA_NOT_FOUND', [
            'data' => ['reserva_id' => $reservaId],
        ]);
    }

    $localizador = trim((string)($reserva['localizador'] ?? ''));
    if ($localizador === '') {
        return vp2_err('La reserva no tiene localizador (Ds_Order).', 'RESERVA_NO_LOCALIZADOR', [
            'data' => ['reserva_id' => $reservaId],
        ]);
    }

    $data = [
        'reserva' => [
            'id'            => (int)$reserva['id'],
            'localizador'   => $localizador,
            'usuario_id'    => (int)($reserva['usuario_id'] ?? 0),
            'estado'        => (string)($reserva['estado'] ?? ''),
            'canal'         => (int)($reserva['canal'] ?? 0),
            'fecha_reserva' => (string)($reserva['fecha_reserva'] ?? ''),
        ],
    ];

    return vp2_ok('Reserva cargada correctamente.', $data);
}
