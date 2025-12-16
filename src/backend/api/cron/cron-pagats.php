<?php

declare(strict_types=1);

global $conn;
/** @var PDO $conn */

$now = date('Y-m-d H:i:s');

// 1) Selecciona pendientes canal web
$sql = "
SELECT id, localizador, fecha_reserva
FROM epgylzqu_parking_finguer_v2.parking_reservas
WHERE canal = 1
  AND estado = 'pendiente'
  AND fecha_reserva >= NOW() - INTERVAL 1 DAY
ORDER BY fecha_reserva ASC
LIMIT 50
";

$stmt = $conn->prepare($sql);
$stmt->execute();

$errors = [];
$processed = 0;

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $id = (int)$row['id'];

    try {
        // 2) Lock atómico
        $lock = $conn->prepare("
            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
            SET estado = 'procesando_pago'
            WHERE id = :id
              AND estado = 'pendiente'
              AND canal = 1
        ");
        $lock->execute([':id' => $id]);

        if ($lock->rowCount() === 0) {
            continue;
        }

        // 3) Flujo completo (modo CRON)
        $result = verificarPagament($id, [
            'solo_info'           => false,
            'actualizar_bd'       => true,
            'enviar_confirmacion' => true,
            'crear_factura'       => true,
            'enviar_factura'      => true,

            'origen'              => 'cron',
            'force_confirmacion'  => false,
            'force_factura_email' => false,
        ]);

        $status = $result['status'] ?? 'error';
        $paid   = (bool)($result['data']['redsys']['paid'] ?? false);

        // 4) Error => volver a pendiente
        if ($status !== 'success') {
            $errors[] = [
                'id'   => $id,
                'step' => $result['step'] ?? null,
                'code' => $result['code'] ?? null,
                'msg'  => $result['message'] ?? 'Error desconocido',
            ];

            $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado = 'pendiente'
                WHERE id = :id AND estado = 'procesando_pago'
            ")->execute([':id' => $id]);

            continue;
        }

        // 5) No pagada => volver a pendiente
        if ($paid === false) {
            $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado = 'pendiente'
                WHERE id = :id AND estado = 'procesando_pago'
            ")->execute([':id' => $id]);

            continue;
        }

        // 6) Pagada: por si acaso, “cerramos” el estado si quedó procesando_pago
        $conn->prepare("
            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
            SET estado = 'pagada'
            WHERE id = :id AND estado = 'procesando_pago'
        ")->execute([':id' => $id]);

        $processed++;
    } catch (Throwable $e) {
        $errors[] = [
            'id'   => $id,
            'step' => 'exception',
            'code' => null,
            'msg'  => $e->getMessage(),
        ];

        // rollback de estado si algo revienta
        $conn->prepare("
            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
            SET estado = 'pendiente'
            WHERE id = :id AND estado = 'procesando_pago'
        ")->execute([':id' => $id]);
    }
}

echo "[CRON] processed={$processed} errors=" . count($errors) . " at {$now}\n";
foreach ($errors as $err) {
    echo "[ERROR] reserva_id={$err['id']} step=" . ($err['step'] ?? '-') . " code=" . ($err['code'] ?? '-') . " msg={$err['msg']}\n";
}
