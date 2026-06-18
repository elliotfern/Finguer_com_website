<?php

declare(strict_types=1);

requireMethod('GET');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

$slug = $routeParams[0];

try {

    // =========================================================
    // slug/clientsAnuals  (listado clientes anuales)
    // finguer.com/api/clients/get/clientsAnuals
    // =========================================================
    if ($slug === 'clientsAnuals') {
        $query = "SELECT 
                        c.nombre AS nom,
                        c.telefono AS telefon,
                        HEX(c.uuid) AS uuid_hex,
                        c.estado,
                        a.fecha_inicio,
                        a.fecha_fin,
                        COALESCE(r.reservas_completadas, 0) AS reservas_completadas
                    FROM usuarios AS c
                    LEFT JOIN usuarios_abonos AS a 
                        ON c.uuid = a.usuario_uuid

                    LEFT JOIN (
                        SELECT 
                            r.usuario_uuid,
                            COUNT(*) AS reservas_completadas
                        FROM parking_reservas r
                        INNER JOIN usuarios_abonos a2 
                            ON a2.usuario_uuid = r.usuario_uuid
                        WHERE r.estado = 'anual'
                        AND r.fecha_reserva BETWEEN a2.fecha_inicio AND a2.fecha_fin
                        GROUP BY r.usuario_uuid
                    ) r 
                        ON r.usuario_uuid = c.uuid

                    WHERE c.tipo_rol = 'cliente_anual'
                    AND c.estado <> 'eliminado'
                    ORDER BY c.nombre ASC;";

        $stmt = $conn->prepare($query);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
        }

        jsonResponse(vp2_ok('OK', [
            'status' => "success",
            'data'  => $rows,
        ]), 200);
    }

    // Si llega aquí, type no válido
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', ['allowed' => ['reserves', 'reservaId', 'verificaPagament']]), 400);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error interno', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
