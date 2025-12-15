<?php

function registrarCobroConfirmado(PDO $conn, int $reservaId, array $pago): array
{
    if ($reservaId <= 0) {
        return vp2_err('ID de reserva no válido.', 'RESERVA_ID_INVALID');
    }

    $metodo   = trim((string)($pago['metodo'] ?? ''));
    $pasarela = trim((string)($pago['pasarela'] ?? ''));
    $ref      = trim((string)($pago['referencia'] ?? ''));
    $importeIn = $pago['importe'] ?? null; // null => usar total_calculado

    if ($metodo === '' || $pasarela === '') {
        return vp2_err('Faltan datos de pago (metodo/pasarela).', 'PAGO_DATA_MISSING', [
            'data' => ['metodo' => $metodo, 'pasarela' => $pasarela],
        ]);
    }

    try {
        $conn->beginTransaction();

        // 1) Cargar reserva + totales (FOR UPDATE para evitar carreras)
        $stmt = $conn->prepare("
            SELECT id, estado, subtotal_calculado, iva_calculado, total_calculado
            FROM epgylzqu_parking_finguer_v2.parking_reservas
            WHERE id = :id
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([':id' => $reservaId]);
        $r = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$r) {
            $conn->rollBack();
            return vp2_err('No se encontró la reserva.', 'RESERVA_NOT_FOUND', [
                'data' => ['reserva_id' => $reservaId],
            ]);
        }

        $subtotal = (float)($r['subtotal_calculado'] ?? 0);
        $iva      = (float)($r['iva_calculado'] ?? 0);
        $totalCalc = (float)($r['total_calculado'] ?? ($subtotal + $iva));

        $importe = ($importeIn === null) ? $totalCalc : (float)$importeIn;
        $estadoReserva = (string)($r['estado'] ?? '');

        // 2) Buscar pago existente (tu tabla ya fuerza 1 pago por reserva)
        $stmtPago = $conn->prepare("
            SELECT id, estado, factura_id
            FROM epgylzqu_parking_finguer_v2.pagos
            WHERE reserva_id = :rid
            LIMIT 1
            FOR UPDATE
        ");
        $stmtPago->execute([':rid' => $reservaId]);
        $pagoRow = $stmtPago->fetch(PDO::FETCH_ASSOC);

        if ($pagoRow) {
            $pagoId = (int)$pagoRow['id'];

            // Actualizamos el mismo registro a confirmado
            $updPago = $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.pagos
                SET
                    fecha = NOW(),
                    metodo = :metodo,
                    importe = :importe,
                    estado = 'confirmado',
                    pasarela = :pasarela,
                    pedido_pasarela = :ref
                WHERE id = :id
            ");
            $updPago->execute([
                ':metodo'  => $metodo,
                ':importe' => $importe,
                ':pasarela' => $pasarela,
                ':ref'     => ($ref !== '' ? $ref : null),
                ':id'      => $pagoId,
            ]);
        } else {
            // Insert nuevo pago confirmado
            $ins = $conn->prepare("
                INSERT INTO epgylzqu_parking_finguer_v2.pagos
                (reserva_id, factura_id, fecha, metodo, importe, estado, pasarela, pedido_pasarela)
                VALUES
                (:rid, NULL, NOW(), :metodo, :importe, 'confirmado', :pasarela, :ref)
            ");
            $ins->execute([
                ':rid'     => $reservaId,
                ':metodo'  => $metodo,
                ':importe' => $importe,
                ':pasarela' => $pasarela,
                ':ref'     => ($ref !== '' ? $ref : null),
            ]);
            $pagoId = (int)$conn->lastInsertId();
        }

        // 3) Marcar reserva pagada (idempotente)
        if ($estadoReserva !== 'pagada') {
            $updRes = $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado = 'pagada'
                WHERE id = :id
            ");
            $updRes->execute([':id' => $reservaId]);
        }

        $conn->commit();

        return vp2_ok('Cobro registrado y reserva marcada como pagada.', [
            'pago' => [
                'id'         => $pagoId,
                'metodo'     => $metodo,
                'pasarela'   => $pasarela,
                'referencia' => ($ref !== '' ? $ref : null),
                'importe'    => $importe,
                'estado'     => 'confirmado',
            ],
            'reserva' => [
                'id'     => $reservaId,
                'estado' => 'pagada',
            ],
        ]);
    } catch (\Throwable $e) {
        if ($conn->inTransaction()) $conn->rollBack();

        return vp2_err('Error registrando cobro.', 'PAGO_DB_ERROR', [
            'data' => [
                'reserva_id' => $reservaId,
                'error'      => $e->getMessage(),
            ],
        ]);
    }
}
