<?php

declare(strict_types=1);

requireMethod('GET');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

$type = (string)($_GET['type'] ?? '');

try {

    // =========================================================
    // type=reserves  (listado por estado_vehiculo)
    // =========================================================
    if ($type === 'reserves') {

        $allowedEstados = ['pendiente_entrada', 'dentro', 'salido'];
        $estadoVehiculo = getEnumParam('estado_vehiculo', $allowedEstados, 'pendiente_entrada');

        $allowedTipos = ['1', '2', '3']; // de momento
        $tipo = isset($_GET['tipo']) ? trim((string)$_GET['tipo']) : '';
        $tipo = ($tipo !== '' && in_array($tipo, $allowedTipos, true)) ? $tipo : null;

        $tipoWhere = $tipo ? " AND pr.tipo = :tipo " : "";

        // Si es "salido", limitamos resultados a 20
        $limitClause = ($estadoVehiculo === 'salido') ? ' LIMIT 20' : '';

        // ORDER BY dinámico
        $orderByField = 'pr.entrada_prevista';
        $orderDirection = 'ASC';

        if ($estadoVehiculo === 'dentro') {
            $orderByField = 'pr.salida_prevista';
            $orderDirection = 'ASC';
        } elseif ($estadoVehiculo === 'salido') {
            $orderByField = 'pr.salida_prevista';
            $orderDirection = 'DESC';
        }

        $query = "SELECT
            pr.localizador,
            pr.estado,
            pr.fecha_reserva,
            pr.canal,

            u.nombre,
            u.telefono,

            DATE(pr.salida_prevista)  AS dataSortida,
            TIME(pr.entrada_prevista) AS HoraEntrada,
            TIME(pr.salida_prevista)  AS HoraSortida,
            DATE(pr.entrada_prevista) AS dataEntrada,

            pr.matricula,
            pr.vehiculo,
            pr.vuelo,

            CASE pr.tipo
                WHEN 1 THEN 'Reserva Finguer class'
                WHEN 2 THEN 'Gold Finguer class'
                WHEN 3 THEN 'Reserva client anual'
                ELSE 'Tipus desconegut'
            END AS tipo,

            pr.estado_vehiculo,
            pr.notas AS notes,

            COALESCE(lx.limpieza, 0) AS limpieza,
            pr.total_calculado AS importe,

            pr.id,

            CASE WHEN px.pago_id IS NULL THEN 0 ELSE 1 END AS processed,

            f.id     AS factura_id,
            f.numero AS factura_numero,
            f.serie  AS factura_serie,

            u.telefono AS tel,
            pr.personas AS numeroPersonas

        FROM parking_reservas pr

        LEFT JOIN usuarios u
            ON pr.usuario_uuid = u.uuid

        LEFT JOIN (
            SELECT reserva_id, MAX(id) AS pago_id
            FROM pagos
            WHERE estado = 'confirmado'
            GROUP BY reserva_id
        ) px ON px.reserva_id = pr.id

        LEFT JOIN (
            SELECT reserva_id, MAX(id) AS factura_id
            FROM facturas
            GROUP BY reserva_id
        ) fx ON fx.reserva_id = pr.id
        LEFT JOIN facturas f
            ON f.id = fx.factura_id

        LEFT JOIN (
            SELECT
                prs_l.reserva_id,
                MAX(
                    CASE
                        WHEN s_l.codigo = 'LIMPIEZA_EXT'     THEN 1
                        WHEN s_l.codigo = 'LIMPIEZA_EXT_INT' THEN 2
                        WHEN s_l.codigo = 'LIMPIEZA_PRO'     THEN 3
                        ELSE 0
                    END
                ) AS limpieza
            FROM parking_reservas_servicios prs_l
            INNER JOIN parking_servicios_catalogo s_l
                ON s_l.id = prs_l.servicio_id
            AND s_l.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')
            GROUP BY prs_l.reserva_id
        ) lx ON lx.reserva_id = pr.id

        WHERE pr.estado_vehiculo = :estado_vehiculo
        AND pr.estado <> 'cancelada'
        {$tipoWhere}
        ORDER BY {$orderByField} {$orderDirection}{$limitClause};";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':estado_vehiculo', $estadoVehiculo, PDO::PARAM_STR);
        if ($tipo !== null) {
            $stmt->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        }
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Contadores por estado_vehiculo
        $counts = [
            'pendiente_entrada' => 0,
            'dentro'            => 0,
            'salido'            => 0,
        ];

        $sqlCounts = "
            SELECT pr.estado_vehiculo, COUNT(*) AS total
            FROM parking_reservas pr
            WHERE 1=1
             AND pr.estado <> 'cancelada'
            " . ($tipo !== null ? " AND pr.tipo = :tipo " : "") . "
            GROUP BY pr.estado_vehiculo
        ";

        $stmtCounts = $conn->prepare($sqlCounts);
        if ($tipo !== null) {
            $stmtCounts->bindValue(':tipo', $tipo, PDO::PARAM_STR);
        }
        $stmtCounts->execute();
        $rowsCounts = $stmtCounts->fetchAll(PDO::FETCH_ASSOC);

        foreach ($rowsCounts as $row) {
            $estado = $row['estado_vehiculo'] ?? '';
            if (isset($counts[$estado])) {
                $counts[$estado] = (int)($row['total'] ?? 0);
            }
        }

        jsonResponse(vp2_ok('OK', [
            'counts' => $counts,
            'rows'   => $rows,
            'hasRows' => (bool)$rows,
        ]));
    }

    // =========================================================
    // type=reservaId (detalle de una reserva por id)
    // =========================================================
    if ($type === 'reservaId') {

        $id = getIntParam('id', true);

        $query = "SELECT
            pr.localizador AS idReserva,
            pr.fecha_reserva AS fechaReserva,

            u.nombre AS clientNom,
            NULL AS clientCognom,

            u.telefono AS telefono,

            DATE(pr.salida_prevista)  AS dataSortida,
            TIME(pr.entrada_prevista) AS HoraEntrada,
            TIME(pr.salida_prevista)  AS HoraSortida,
            DATE(pr.entrada_prevista) AS dataEntrada,

            pr.matricula,
            pr.vehiculo AS modelo,
            pr.vuelo,
            pr.tipo,

            CASE WHEN pr.estado_vehiculo IN ('dentro','salido') THEN 1 ELSE 0 END AS checkIn,
            CASE WHEN pr.estado_vehiculo = 'salido' THEN 1 ELSE 0 END AS checkOut,

            pr.notas AS notes,
            pr.canal AS buscadores,

            COALESCE(lx.limpieza, 0) AS limpieza,

            COALESCE(p.importe, 0) AS importe,

            pr.id,

            CASE WHEN px.pago_id IS NULL THEN 0 ELSE 1 END AS processed,

            f.id     AS factura_id,
            f.numero AS factura_numero,
            f.serie  AS factura_serie,

            u.nombre,
            u.telefono AS tel,
            pr.personas AS numeroPersonas,

            u.dispositiu,
            u.navegador,
            u.sistema_operatiu,
            u.ip

        FROM parking_reservas pr

        LEFT JOIN usuarios u
            ON pr.usuario_uuid = u.uuid

        LEFT JOIN (
            SELECT reserva_id, MAX(id) AS pago_id
            FROM pagos
            WHERE estado='confirmado'
            GROUP BY reserva_id
        ) px ON px.reserva_id = pr.id

        LEFT JOIN pagos p
            ON p.id = px.pago_id

        LEFT JOIN (
            SELECT reserva_id, MAX(id) AS factura_id
            FROM facturas
            GROUP BY reserva_id
        ) fx ON fx.reserva_id = pr.id

        LEFT JOIN facturas f
            ON f.id = fx.factura_id

        LEFT JOIN (
            SELECT
                prs_l.reserva_id,
                MAX(
                    CASE
                        WHEN s_l.codigo = 'LIMPIEZA_EXT'     THEN 1
                        WHEN s_l.codigo = 'LIMPIEZA_EXT_INT' THEN 2
                        WHEN s_l.codigo = 'LIMPIEZA_PRO'     THEN 3
                        ELSE 0
                    END
                ) AS limpieza
            FROM parking_reservas_servicios prs_l
            INNER JOIN parking_servicios_catalogo s_l
                ON s_l.id = prs_l.servicio_id
            AND s_l.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')
            GROUP BY prs_l.reserva_id
        ) lx ON lx.reserva_id = pr.id

        WHERE pr.id = :id
        LIMIT 1;";

        $stmt = $conn->prepare($query);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        // Mantengo compat con tu frontend: array con 1 elemento
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$rows) {
            jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
        }

        jsonResponse(vp2_ok('OK', [
            'rows' => $rows
        ]));
    }

    // =========================================================
    // type=verificaPagament (info sin efectos secundarios)
    // =========================================================
    if ($type === 'verificaPagament') {

        $id = $_GET['id'] ?? null;
        if ($id === null || $id === '') {
            jsonResponse(vp2_err('Falta parámetro id', 'MISSING_ID'), 400);
        }

        $result = verificarPagament($id, [
            'solo_info'           => true,
            'actualizar_bd'       => false,
            'enviar_confirmacion' => false,
            'crear_factura'       => false,
            'enviar_factura'      => false,
        ]);

        $http = (($result['status'] ?? '') === 'success') ? 200 : 400;

        // Si verificarPagament ya devuelve vp2_ok/vp2_err perfecto,
        // lo devolvemos tal cual:
        jsonResponse($result, $http);
    }

    // =========================================================
    // type=list-by-email  (reservas por email cliente)
    // GET ?type=list-by-email&email=...&limit=50&offset=0
    // =========================================================
    if ($type === 'list-by-email') {

        $email = isset($_GET['email']) ? trim((string)$_GET['email']) : '';
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            jsonResponse(vp2_err('Email inválido', 'BAD_EMAIL'), 400);
        }

        $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        if ($limit < 1) $limit = 50;
        if ($limit > 200) $limit = 200;
        if ($offset < 0) $offset = 0;

        // LIST
        $sql = "
        SELECT
            r.id,
            r.localizador,
            r.estado,
            r.estado_vehiculo,
            r.fecha_reserva,
            r.entrada_prevista,
            r.salida_prevista,
            r.total_calculado,
            r.vehiculo,
            r.matricula,
            r.created_at
        FROM parking_reservas r
        INNER JOIN usuarios u
            ON u.uuid = r.usuario_uuid
        WHERE LOWER(u.email) = LOWER(:email)
        ORDER BY r.fecha_reserva DESC
        LIMIT :limit OFFSET :offset
    ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // COUNT
        $sqlCount = "
        SELECT COUNT(*) AS total
        FROM parking_reservas r
        INNER JOIN usuarios u
            ON u.uuid = r.usuario_uuid
        WHERE LOWER(u.email) = LOWER(:email)
    ";
        $stmtC = $conn->prepare($sqlCount);
        $stmtC->bindValue(':email', $email, PDO::PARAM_STR);
        $stmtC->execute();
        $total = (int)($stmtC->fetchColumn() ?: 0);

        jsonResponse(vp2_ok('OK', [
            'email'  => $email,
            'limit'  => $limit,
            'offset' => $offset,
            'total'  => $total,
            'rows'   => $rows,
            'hasRows' => (bool)$rows,
        ]), 200);
    }


    // Si llega aquí, type no válido
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', ['allowed' => ['reserves', 'reservaId', 'verificaPagament']]), 400);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error interno', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
