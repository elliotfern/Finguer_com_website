<?php

header('Content-Type: application/json; charset=utf-8');

// Verificar si el método de la solicitud es GET
if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    header('HTTP/1.1 405 Method Not Allowed');
    echo json_encode(['error' => 'Method not allowed']);
    exit();
} else {
    // Verificar si el token está presente en las cookies
    if (isset($_COOKIE['token'])) {
        $token = $_COOKIE['token'];

        // Verificar el token aquí según tus requerimientos
        if (validarToken($token)) {

            // ENDPOINT - Llistat factures amb paginació
            // URL -> https://finguer.com/api/factures/get/?type=facturacioLlistat
            if (isset($_GET['type']) && $_GET['type'] == 'facturacioLlistat') {

                // --- Parámetros de paginación y búsqueda ---
                $page     = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
                $perPage  = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 50;
                $perPage  = ($perPage > 0 && $perPage <= 100) ? $perPage : 50;
                $search   = trim($_GET['q'] ?? '');
                $export  = $_GET['export'] ?? '';

                // --- Construcción del WHERE opcional ---
                $conditions = [];
                $params     = [];

                if ($search !== '') {
                    // Buscador simple por número, serie, nombre, empresa, NIF, email
                    $conditions[] = "(
                        f.numero LIKE :q
                        OR f.serie LIKE :q
                        OR f.facturar_a_nombre LIKE :q
                        OR f.facturar_a_empresa LIKE :q
                        OR f.facturar_a_nif LIKE :q
                        OR f.facturar_a_email LIKE :q
                    )";
                    $params[':q'] = '%' . $search . '%';
                }

                $whereSql = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

                // --- Contar total ---
                $sqlCount = "
                    SELECT COUNT(*) AS total
                    FROM epgylzqu_parking_finguer_v2.facturas f
                    $whereSql
                ";
                $stmtCount = $conn->prepare($sqlCount);
                $stmtCount->execute($params);
                $total = (int)$stmtCount->fetchColumn();

                // 👉 MODO CSV: ignoramos paginación y devolvemos todo
                if ($export === 'csv') {
                    $sqlCsv = "
                            SELECT
                                f.id,
                                f.numero,
                                f.serie,
                                f.fecha_emision,
                                f.subtotal,
                                f.impuesto_total,
                                f.total,
                                f.estado,
                                f.facturar_a_nombre,
                                f.facturar_a_empresa,
                                f.facturar_a_nif,
                                f.facturar_a_email
                            FROM epgylzqu_parking_finguer_v2.facturas f
                            $whereSql
                            ORDER BY f.fecha_emision ASC, f.serie ASC, f.numero ASC
                        ";

                    $stmtCsv = $conn->prepare($sqlCsv);
                    foreach ($params as $key => $value) {
                        $stmtCsv->bindValue($key, $value, PDO::PARAM_STR);
                    }
                    $stmtCsv->execute();
                    $rows = $stmtCsv->fetchAll(PDO::FETCH_ASSOC);

                    $filename = 'factures_' . date('Y-m-d') . '.csv';

                    header('Content-Type: text/csv; charset=UTF-8');
                    header('Content-Disposition: attachment; filename="' . $filename . '"');

                    // BOM UTF-8 per Excel
                    echo "\xEF\xBB\xBF";

                    $out = fopen('php://output', 'w');

                    fputcsv($out, [
                        'ID',
                        'Serie',
                        'Numero',
                        'Fecha emision',
                        'Cliente',
                        'NIF',
                        'Email',
                        'Subtotal',
                        'Impuesto total',
                        'Total',
                        'Estado',
                    ], ';');

                    foreach ($rows as $f) {
                        $cliente = $f['facturar_a_nombre'];
                        if (!empty($f['facturar_a_empresa'])) {
                            $cliente = $f['facturar_a_empresa'] . ' - ' . $cliente;
                        }

                        fputcsv($out, [
                            $f['id'],
                            $f['serie'],
                            $f['numero'],
                            $f['fecha_emision'],
                            $cliente,
                            $f['facturar_a_nif'],
                            $f['facturar_a_email'],
                            number_format((float)$f['subtotal'], 2, ',', ''),
                            number_format((float)$f['impuesto_total'], 2, ',', ''),
                            number_format((float)$f['total'], 2, ',', ''),
                            $f['estado'],
                        ], ';');
                    }

                    fclose($out);
                    exit;
                }

                $totalPages = $total > 0 ? (int)ceil($total / $perPage) : 1;
                $page = min($page, $totalPages);
                $offset = ($page - 1) * $perPage;

                // --- Obtener filas ---
                $sql = "
                        SELECT
                            f.id,
                            f.numero,
                            f.serie,
                            f.fecha_emision,
                            f.subtotal,
                            f.impuesto_total,
                            f.total,
                            f.estado,
                            f.facturar_a_nombre,
                            f.facturar_a_empresa,
                            f.facturar_a_nif,
                            f.facturar_a_email
                        FROM epgylzqu_parking_finguer_v2.facturas f
                        $whereSql
                        ORDER BY f.fecha_emision DESC, f.serie DESC, f.numero DESC
                        LIMIT :limit OFFSET :offset
                    ";

                $stmt = $conn->prepare($sql);

                // bind de params de búsqueda
                foreach ($params as $key => $value) {
                    $stmt->bindValue($key, $value, PDO::PARAM_STR);
                }

                $stmt->bindValue(':limit',  $perPage, PDO::PARAM_INT);
                $stmt->bindValue(':offset', $offset,  PDO::PARAM_INT);

                $stmt->execute();
                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // Formateo ligero para el frontend
                $data = [];
                foreach ($rows as $f) {
                    $cliente = $f['facturar_a_nombre'];
                    if (!empty($f['facturar_a_empresa'])) {
                        $cliente = $f['facturar_a_empresa'] . ' - ' . $cliente;
                    }

                    $data[] = [
                        'id'         => (int)$f['id'],
                        'serie'      => $f['serie'],
                        'numero'     => $f['numero'],
                        'numeroVisible' => $f['serie'] . '/' . $f['numero'],
                        'fechaEmision'  => $f['fecha_emision'],
                        'cliente'    => $cliente,
                        'nif'        => $f['facturar_a_nif'],
                        'email'      => $f['facturar_a_email'],
                        'subtotal'   => (float)$f['subtotal'],
                        'iva'        => (float)$f['impuesto_total'],
                        'total'      => (float)$f['total'],
                        'estado'     => $f['estado'],
                    ];
                }

                echo json_encode([
                    'success'    => true,
                    'page'       => $page,
                    'perPage'    => $perPage,
                    'total'      => $total,
                    'totalPages' => $totalPages,
                    'search'     => $search,
                    'data'       => $data,
                ]);

                // ENDPOINT - Verificar integritat de la cadena de factures
                // URL -> https://finguer.com/api/factures/get/?type=facturacioVerificarIntegridad
            } else if (isset($_GET['type']) && $_GET['type'] === 'facturacioVerificarIntegridad') {
                header('Content-Type: application/json; charset=utf-8');
                global $conn;

                try {
                    $resultado = verificarIntegridadFacturas($conn);

                    echo json_encode([
                        'success' => true,
                        'data'    => $resultado,
                    ], JSON_UNESCAPED_UNICODE);
                } catch (Throwable $e) {
                    echo json_encode([
                        'success' => false,
                        'error'   => 'Error verificant la integritat de factures.',
                        'detail'  => $e->getMessage(),
                    ]);
                }

                exit;
            }
        } else {
            // Token inválido
            header('HTTP/1.1 403 Forbidden');
            echo json_encode(['error' => 'Invalid token']);
            exit();
        }
    } else {
        // No se proporcionó un token
        header('HTTP/1.1 403 Forbidden');
        echo json_encode(['error' => 'Access not allowed']);
        exit();
    }
}
