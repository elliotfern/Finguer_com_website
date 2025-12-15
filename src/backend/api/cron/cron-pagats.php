<?php

declare(strict_types=1);

global $conn;
/** @var PDO $conn */

$now = date('Y-m-d H:i:s');

// 1) Selecciona pendientes canal web (NO solo 5 minutos)
//    Limita por antigüedad y por lote para no cargar el server
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
        // 2) LOCK atómico: marcamos como "procesando_pago"
        $lock = $conn->prepare("
            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
            SET estado = 'procesando_pago'
            WHERE id = :id
              AND estado = 'pendiente'
              AND canal = 1
        ");
        $lock->execute([':id' => $id]);

        if ($lock->rowCount() === 0) {
            // ya lo está procesando otro cron, o cambió de estado
            continue;
        }

        // 3) Verificar pago (debe decidir y devolver resultado claro)
        $result = verificarPagamentRedsys($id, true);

        // 4) Si NO está pagada todavía, vuelve a 'pendiente' (o a 'pendiente_pago')
        if (is_array($result) && ($result['paid'] ?? false) === false) {
            $back = $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado = 'pendiente'
                WHERE id = :id AND estado = 'procesando_pago'
            ");
            $back->execute([':id' => $id]);
            continue;
        }

        // 5) Si hubo error, registra y vuelve a pendiente (o error_pago)
        if (is_array($result) && ($result['status'] ?? '') === 'error') {
            $errors[] = ['id' => $id, 'error' => $result['message'] ?? 'Error desconocido'];

            $back = $conn->prepare("
                UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                SET estado = 'pendiente'
                WHERE id = :id AND estado = 'procesando_pago'
            ");
            $back->execute([':id' => $id]);
            continue;
        }

        // si llegó aquí: pagada y ok
        $processed++;
    } catch (Throwable $e) {
        $errors[] = ['id' => $id, 'error' => $e->getMessage()];

        // rollback de estado si algo revienta
        $back = $conn->prepare("
            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
            SET estado = 'pendiente'
            WHERE id = :id AND estado = 'procesando_pago'
        ");
        $back->execute([':id' => $id]);
    }
}

// salida tipo cron (texto)
echo "[CRON] processed={$processed} errors=" . count($errors) . " at {$now}\n";
if ($errors) {
    foreach ($errors as $err) {
        echo "[ERROR] reserva_id={$err['id']} msg={$err['error']}\n";
    }
}
