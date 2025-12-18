<?php

/**
 * Registra un event en el log de factures.
 *
 * @param PDO   $conn
 * @param int   $facturaId   ID de la factura
 * @param int|null $usuarioId ID de l'usuari que fa l'acció (o null si és automàtic)
 * @param string   $accion    Nom curt de l'acció: 'creacion', 'envio_email', 'anulacion', etc.
 * @param array    $detalles  Informació extra que vulguis guardar (es desa com JSON)
 */
function registrarLogFactura(PDO $conn, int $facturaId, ?int $usuarioId, string $accion, array $detalles = []): void
{
    $sql = "
        INSERT INTO facturas_logs
        (factura_id, usuario_id, accion, detalles_json)
        VALUES (:factura_id, :usuario_id, :accion, :detalles_json)
    ";

    $stmt = $conn->prepare($sql);

    $jsonDetalles = !empty($detalles)
        ? json_encode($detalles, JSON_UNESCAPED_UNICODE)
        : null;

    $stmt->execute([
        ':factura_id'    => $facturaId,
        ':usuario_id'    => $usuarioId,
        ':accion'        => $accion,
        ':detalles_json' => $jsonDetalles,
    ]);
}
