<?php

declare(strict_types=1);

requireMethod('GET');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

// Solo admin (por ahora)
$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    jsonResponse(vp2_err('No autoritzat', 'FORBIDDEN'), 403);
}

$type = (string)($_GET['type'] ?? '');

try {

    // =========================================================
    // type=list  (listado usuarios)
    // =========================================================
    if ($type === 'list') {

        // Paginación
        $limit  = isset($_GET['limit']) ? (int)$_GET['limit'] : 50;
        $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;

        if ($limit < 1) $limit = 50;
        if ($limit > 200) $limit = 200;
        if ($offset < 0) $offset = 0;

        // Búsqueda
        $q = isset($_GET['q']) ? trim((string)$_GET['q']) : '';
        $qLike = '%' . $q . '%';

        // Filtro opcional por rol (si quieres)
        $allowedRoles = ['admin', 'trabajador', 'cliente', 'cliente_anual'];
        $role = isset($_GET['role']) ? trim((string)$_GET['role']) : '';
        $role = ($role !== '' && in_array($role, $allowedRoles, true)) ? $role : null;

        $roleWhere = $role ? " AND u.tipo_rol = :role " : "";

        // List
        $sql = "
            SELECT
                u.uuid,
                u.nombre,
                u.email,
                u.telefono,
                u.tipo_rol,
                u.created_at
            FROM usuarios u
            WHERE 1=1
              AND (:q = '' OR u.nombre LIKE :qLike OR u.email LIKE :qLike OR u.telefono LIKE :qLike)
              {$roleWhere}
            ORDER BY u.created_at DESC
            LIMIT :limit OFFSET :offset
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':q', $q, PDO::PARAM_STR);
        $stmt->bindValue(':qLike', $qLike, PDO::PARAM_STR);
        if ($role !== null) {
            $stmt->bindValue(':role', $role, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();

        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Count (para paginación)
        $sqlCount = "
                SELECT COUNT(*) AS total
                FROM usuarios u
                WHERE 1=1
                AND (:q = '' OR u.nombre LIKE :qLike OR u.email LIKE :qLike OR u.telefono LIKE :qLike)
                {$roleWhere}
            ";

        $stmtC = $conn->prepare($sqlCount);
        $stmtC->bindValue(':q', $q, PDO::PARAM_STR);
        $stmtC->bindValue(':qLike', $qLike, PDO::PARAM_STR);
        if ($role !== null) {
            $stmtC->bindValue(':role', $role, PDO::PARAM_STR);
        }
        $stmtC->execute();

        $total = (int)($stmtC->fetchColumn() ?: 0);

        // Normalizar uuid a string para el frontend
        $items = [];
        foreach ($rows as $r) {
            $uuidBin = $r['uuid'] ?? null;
            if (!is_string($uuidBin) || strlen($uuidBin) !== 16) {
                // si hay algún registro raro, lo saltamos
                continue;
            }

            $items[] = [
                'uuid'      => uuid_string_from_bin($uuidBin),
                'nombre'    => (string)($r['nombre'] ?? ''),
                'email'     => (string)($r['email'] ?? ''),
                'telefono'  => (string)($r['telefono'] ?? ''),
                'tipo_rol'  => (string)($r['tipo_rol'] ?? ''),
                'createdAt' => $r['created_at'] ?? null,
            ];
        }

        jsonResponse(vp2_ok('OK', [
            'q'       => $q,
            'role'    => $role,
            'limit'   => $limit,
            'offset'  => $offset,
            'total'   => $total,
            'rows'    => $items,
            'hasRows' => (bool)$items,
        ]));
    }

        // =========================================================
    // type=get  (detalle usuario por uuid)
    // GET ?type=get&uuid=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
    // =========================================================
    if ($type === 'get') {

        $uuidStr = isset($_GET['uuid']) ? trim((string)$_GET['uuid']) : '';
        if ($uuidStr === '') {
            jsonResponse(vp2_err('Falta parámetro uuid', 'BAD_UUID'), 400);
        }

        try {
            $uuidBin = uuid_bin_from_string($uuidStr);
        } catch (Throwable $e) {
            jsonResponse(vp2_err('UUID inválido', 'BAD_UUID'), 400);
        }

        $sql = "
            SELECT
                u.uuid,
                u.nombre,
                u.email,
                u.estado,
                u.empresa,
                u.nif,
                u.direccion,
                u.ciudad,
                u.codigo_postal,
                u.pais,
                u.telefono,
                u.anualitat,
                u.tipo_rol,
                u.locale,
                u.dispositiu,
                u.navegador,
                u.sistema_operatiu,
                u.ip,
                u.created_at,
                u.updated_at
            FROM usuarios u
            WHERE u.uuid = :uuid
            LIMIT 1
        ";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();

        $r = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$r) {
            jsonResponse(vp2_err('Usuario no encontrado', 'NOT_FOUND'), 404);
        }

        $uuidBinRow = $r['uuid'] ?? null;
        if (!is_string($uuidBinRow) || strlen($uuidBinRow) !== 16) {
            jsonResponse(vp2_err('UUID almacenado inválido', 'DATA_ERROR'), 500);
        }

        $data = [
            'uuid'             => uuid_string_from_bin($uuidBinRow),
            'nombre'           => (string)($r['nombre'] ?? ''),
            'email'            => (string)($r['email'] ?? ''),
            'estado'           => (string)($r['estado'] ?? ''),
            'empresa'          => $r['empresa'] ?? null,
            'nif'              => $r['nif'] ?? null,
            'direccion'        => $r['direccion'] ?? null,
            'ciudad'           => $r['ciudad'] ?? null,
            'codigo_postal'    => $r['codigo_postal'] ?? null,
            'pais'             => $r['pais'] ?? null,
            'telefono'         => $r['telefono'] ?? null,
            'anualitat'        => $r['anualitat'] ?? null,
            'tipo_rol'         => (string)($r['tipo_rol'] ?? ''),
            'locale'           => (string)($r['locale'] ?? ''),
            'dispositiu'       => $r['dispositiu'] ?? null,
            'navegador'        => $r['navegador'] ?? null,
            'sistema_operatiu' => $r['sistema_operatiu'] ?? null,
            'ip'               => $r['ip'] ?? null,
            'createdAt'        => $r['created_at'] ?? null,
            'updatedAt'        => $r['updated_at'] ?? null,
        ];

        jsonResponse(vp2_ok('OK', $data), 200);
    }


    // Si llega aquí, type no válido
    jsonResponse(vp2_err('type inválido', 'BAD_TYPE', [
        'allowed' => ['list', 'get']
    ]), 400);
} catch (Throwable $e) {
    jsonResponse(vp2_err('Error interno', 'SERVER_ERROR', [
        'details' => $e->getMessage(),
    ]), 500);
}
