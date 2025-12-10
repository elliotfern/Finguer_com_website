<?php

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
            // 1) Llistat reserves (pendientes)
            if (isset($_GET['type']) && $_GET['type'] == 'reserves') {
                $data = array();
                global $conn;
                /** @var PDO $conn */

                // --- 1) Determinar estado_vehiculo a partir del parámetro GET ---
                $allowedEstados = ['pendiente_entrada', 'dentro', 'salido'];

                if (isset($_GET['estado_vehiculo']) && in_array($_GET['estado_vehiculo'], $allowedEstados, true)) {
                    $estadoVehiculo = $_GET['estado_vehiculo'];
                } else {
                    // valor por defecto si no se pasa nada o es inválido
                    $estadoVehiculo = 'pendiente_entrada';
                }

                // Si es "salido", limitamos resultados a 20
                $limitClause = '';
                if ($estadoVehiculo === 'salido') {
                    $limitClause = ' LIMIT 20';
                }

                // ORDER BY dinámico
                // - por defecto: entrada_prevista ASC
                // - si está "dentro": salida_prevista ASC
                // - si está "salido": salida_prevista DESC (últimos 20)
                $orderByField = 'pr.entrada_prevista';
                $orderDirection = 'ASC';

                if ($estadoVehiculo === 'dentro') {
                    $orderByField = 'pr.salida_prevista';
                    $orderDirection = 'ASC';
                } elseif ($estadoVehiculo === 'salido') {
                    $orderByField = 'pr.salida_prevista';
                    $orderDirection = 'DESC';
                }

                // --- 2) Query con placeholder ---
                $query = "SELECT
                -- Identificadors bàsics
                pr.localizador,
                pr.estado,
                pr.fecha_reserva,

                -- Nom i cognom del client (aproximat a partir de u.nombre)
                u.nombre,

                u.telefono,
                pr.canal,

                -- Dates i hores d'entrada/sortida
                DATE(pr.salida_prevista)      AS dataSortida,
                TIME(pr.entrada_prevista)     AS HoraEntrada,
                TIME(pr.salida_prevista)      AS HoraSortida,
                DATE(pr.entrada_prevista)     AS dataEntrada,

                -- Vehicle
                pr.matricula,
                pr.vehiculo,
                pr.vuelo,
                pr.tipo,
                -- Descripció humana del tipus de reserva
                CASE pr.tipo
                    WHEN 1 THEN 'Reserva Finguer class'
                    WHEN 2 THEN 'Gold Finguer class'
                    WHEN 3 THEN 'Reserva client anual'
                    ELSE 'Tipus desconegut'
                END                           AS tipo,

                -- Estat del vehicle (mapejat als codis antics)
                pr.estado_vehiculo,

                pr.notas                      AS notes,
                pr.canal                      AS buscadores,

                -- Codi de neteja (0/1/2/3) derivat dels serveis
                CASE
                    WHEN s_l.codigo = 'LIMPIEZA_EXT'      THEN 1
                    WHEN s_l.codigo = 'LIMPIEZA_EXT_INT'  THEN 2
                    WHEN s_l.codigo = 'LIMPIEZA_PRO'      THEN 3
                    ELSE 0
                END                           AS limpieza,

                -- Import: import calculat
                pr.total_calculado            AS importe,

                pr.id,

                -- processed: 1 si hi ha pagament confirmat, 0 si no
                CASE 
                    WHEN p.id IS NULL THEN 0 
                    ELSE 1 
                END                           AS processed,

                -- Dades de la factura
                f.id      AS factura_id,
                f.numero  AS factura_numero,
                f.serie   AS factura_serie,

                u.nombre,
                u.telefono                    AS tel,
                pr.personas                   AS numeroPersonas

                FROM epgylzqu_parking_finguer_v2.parking_reservas pr

                -- Client
                LEFT JOIN epgylzqu_parking_finguer_v2.usuarios u
                    ON pr.usuario_id = u.id

                -- Pagament Redsys (si existeix i està confirmat)
                LEFT JOIN epgylzqu_parking_finguer_v2.pagos p
                    ON p.reserva_id = pr.id
                AND p.estado = 'confirmado'

                -- Factura (si existeix)
                LEFT JOIN epgylzqu_parking_finguer_v2.facturas AS f ON f.reserva_id = pr.id

                -- Servei de neteja (si n'hi ha)
                LEFT JOIN epgylzqu_parking_finguer_v2.parking_reservas_servicios prs_l
                    ON prs_l.reserva_id = pr.id
                LEFT JOIN epgylzqu_parking_finguer_v2.parking_servicios_catalogo s_l
                    ON s_l.id = prs_l.servicio_id
                AND s_l.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')

                -- Filtrar por estado_vehiculo dinámico
                WHERE pr.estado_vehiculo = :estado_vehiculo
           
                ORDER BY {$orderByField} {$orderDirection}{$limitClause};";

                $stmt = $conn->prepare($query);
                $stmt->bindValue(':estado_vehiculo', $estadoVehiculo, PDO::PARAM_STR);
                $stmt->execute();

                $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // --- 3) Contadores por estado_vehiculo ---
                // Nota: aquí aplicamos la misma lógica de exclusión de anuals
                // para el estado "pendiente_entrada"
                $counts = [
                    'pendiente_entrada' => 0,
                    'dentro'            => 0,
                    'salido'            => 0,
                ];

                $sqlCounts = "
                    SELECT
                        estado_vehiculo,
                        COUNT(*) AS total
                    FROM epgylzqu_parking_finguer_v2.parking_reservas
                    GROUP BY estado_vehiculo
                ";

                $stmtCounts = $conn->query($sqlCounts);
                $rowsCounts = $stmtCounts->fetchAll(PDO::FETCH_ASSOC);

                foreach ($rowsCounts as $row) {
                    $estado = $row['estado_vehiculo'];
                    if (isset($counts[$estado])) {
                        $counts[$estado] = (int) $row['total'];
                    }
                }

                header('Content-Type: application/json; charset=utf-8');

                echo json_encode([
                    'counts' => $counts,
                    'rows'   => $data,
                    // opcional: para mantener compatibilidad con el mensaje anterior,
                    // puedes añadir esto:
                    'hasRows' => (bool) $data,
                ]);


                // endpoint ReservaId
            } else if (isset($_GET['type']) && $_GET['type'] == 'reservaId') {
                header('Content-Type: application/json; charset=utf-8');

                $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
                if ($id === false || $id === null) {
                    http_response_code(400);
                    echo json_encode(['error' => 'Parámetro id inválido']);
                    exit;
                }

                $data = array();
                global $conn;
                /** @var PDO $conn */
                $query = "SELECT 
                    pr.localizador                          AS idReserva,

                    -- Fecha de creación de la reserva
                    pr.fecha_reserva                        AS fechaReserva,

                    -- Nombre / apellidos del cliente (si ya no están en la reserva,
                    -- los tomamos de usuarios; ajusta si conservas columnas separadas)
                    u.nombre                                AS clientNom,
                    NULL                                    AS clientCognom,

                    -- Teléfono (prioridad al de usuarios)
                    u.telefono                              AS telefono,

                    -- Fechas y horas previstas
                    DATE(pr.salida_prevista)                AS dataSortida,
                    TIME(pr.entrada_prevista)               AS HoraEntrada,
                    TIME(pr.salida_prevista)                AS HoraSortida,
                    DATE(pr.entrada_prevista)               AS dataEntrada,

                    -- Vehículo
                    pr.matricula,
                    pr.vehiculo                             AS modelo,
                    pr.vuelo,
                    pr.tipo,

                    -- Estados check-in / check-out derivados de estado_vehiculo
                    CASE 
                        WHEN pr.estado_vehiculo IN ('dentro', 'salido') THEN 1
                        ELSE 0
                    END                                     AS checkIn,
                    CASE 
                        WHEN pr.estado_vehiculo = 'salido' THEN 1
                        ELSE 0
                    END                                     AS checkOut,

                    -- Notas y canal
                    pr.notas                                AS notes,
                    pr.canal                                AS buscadores,

                    -- Limpieza (derivada de los servicios asociados)
                    CASE
                        WHEN s_l.codigo = 'LIMPIEZA_EXT'      THEN 1
                        WHEN s_l.codigo = 'LIMPIEZA_EXT_INT'  THEN 2
                        WHEN s_l.codigo = 'LIMPIEZA_PRO'      THEN 3
                        ELSE 0
                    END                                     AS limpieza,

                    -- Importe pagado (pagos confirmados)
                    COALESCE(p.importe, 0)                  AS importe,

                    -- ID interno de la reserva
                    pr.id,

                    -- processed: 1 si hay pago Redsys confirmado
                    CASE 
                        WHEN p.id IS NULL THEN 0 
                        ELSE 1 
                    END                                     AS processed,

                    -- Datos usuario
                    u.nombre,
                    u.telefono                              AS tel,
                    pr.personas                             AS numeroPersonas,
                    u.dispositiu,
                    u.navegador,
                    u.sistema_operatiu,
                    u.ip

                FROM parking_reservas pr

                LEFT JOIN usuarios u 
                    ON pr.usuario_id = u.id

                LEFT JOIN pagos p
                    ON p.reserva_id = pr.id
                    AND p.estado = 'confirmado'

                LEFT JOIN parking_reservas_servicios prs_l
                    ON prs_l.reserva_id = pr.id

                LEFT JOIN parking_servicios_catalogo s_l
                    ON s_l.id = prs_l.servicio_id
                    AND s_l.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')

                WHERE pr.id = :id;";

                $stmt = $conn->prepare($query);
                $stmt->bindValue(':id', $id, PDO::PARAM_INT);
                $stmt->execute();

                $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

                if (!$rows) {
                    // Puedes devolver [] o un objeto con mensaje; tu frontend ya tolera [].
                    http_response_code(404);
                    echo json_encode([]);
                    exit;
                }

                echo json_encode($rows);
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
