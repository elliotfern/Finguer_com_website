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

                // --- 2) Query con placeholder ---
                $query = "SELECT
                -- Identificadors bàsics
                pr.localizador,
                pr.fecha_reserva,

                -- Nom i cognom del client (aproximat a partir de u.nombre)
                u.nombre,

                u.telefono,

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

                -- Import: import pagat a Redsys (si hi ha un pagament confirmat), si no 0
                COALESCE(p.importe, 0)        AS importe,

                pr.id,

                -- processed: 1 si hi ha pagament confirmat, 0 si no
                CASE 
                    WHEN p.id IS NULL THEN 0 
                    ELSE 1 
                END                           AS processed,

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

            -- Servei de neteja (si n'hi ha)
            LEFT JOIN epgylzqu_parking_finguer_v2.parking_reservas_servicios prs_l
                ON prs_l.reserva_id = pr.id
            LEFT JOIN epgylzqu_parking_finguer_v2.parking_servicios_catalogo s_l
                ON s_l.id = prs_l.servicio_id
            AND s_l.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')

            -- Filtrar por estado_vehiculo dinámico
            WHERE pr.estado_vehiculo = :estado_vehiculo

            ORDER BY pr.entrada_prevista ASC;";

                $stmt = $conn->prepare($query);
                $stmt->bindValue(':estado_vehiculo', $estadoVehiculo, PDO::PARAM_STR);
                $stmt->execute();

                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($data = $stmt->fetchAll(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');

                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
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
                $query = "SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tel AS 'telefono',
                        rc1.diaSalida AS 'dataSortida',
                        rc1.horaEntrada AS 'HoraEntrada',
                        rc1.horaSalida AS 'HoraSortida',
                        rc1.diaEntrada AS 'dataEntrada',
                        rc1.matricula,
                        rc1.vehiculo AS 'modelo',
                        rc1.vuelo,
                        rc1.tipo,
                        rc1.checkIn,
                        rc1.checkOut,
                        rc1.notes,
                        rc1.buscadores,
                        rc1.limpieza,
                        rc1.importe,
                        rc1.id,
                        rc1.processed,
                        u.nombre,
                        u.telefono AS tel,
                        rc1.numeroPersonas,
                        u.dispositiu,
                        u.navegador,
                        u.sistema_operatiu,
                        u.ip
                        FROM reserves_parking AS rc1
                        LEFT JOIN usuaris AS u ON rc1.idClient = u.id
                        WHERE rc1.id = :id";

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

            // 3) Numero reserves pendents
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesPendents') {
                $query = "SELECT COUNT(r.localizador) AS numero
                        FROM parking_reservas as r
                        WHERE r.estado_vehiculo = 'pendiente_entrada'";

                // Preparar la consulta
                $stmt = $conn->prepare($query);

                // Ejecutar la consulta
                $stmt->execute();

                // Verificar si se encontraron resultados
                if ($stmt->rowCount() === 0) {
                    echo json_encode(['error' => 'No rows found']);
                    exit();
                }

                // Recopilar los resultados
                $data = $stmt->fetch(PDO::FETCH_ASSOC);

                // Establecer el encabezado de respuesta a JSON
                header('Content-Type: application/json');

                // Devolver los datos en formato JSON
                echo json_encode($data);
                exit();
            }
            // 4) Reserves al parking
            elseif (isset($_GET['type']) && $_GET['type'] == 'parking') {
                $data = array();
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tel AS 'telefono',
                        rc1.diaSalida AS 'dataSortida',
                        rc1.horaEntrada AS 'HoraEntrada',
                        rc1.horaSalida AS 'HoraSortida',
                        rc1.diaEntrada AS 'dataEntrada',
                        rc1.matricula AS 'matricula',
                        rc1.vehiculo AS 'modelo',
                        rc1.vuelo,
                        rc1.tipo,
                        rc1.checkIn,
                        rc1.checkOut,
                        rc1.notes,
                        rc1.buscadores,
                        rc1.limpieza,
                        rc1.id,
                        rc1.importe,
                        rc1.processed,
                        u.nombre,
                        u.telefono AS tel
                        FROM reserves_parking AS rc1
                        LEFT JOIN reservas_buscadores AS b ON rc1.buscadores = b.id
                        LEFT JOIN usuaris AS u ON rc1.idClient = u.id
                        WHERE rc1.checkIn = 1
                        GROUP BY rc1.id
                        ORDER BY rc1.diaSalida ASC, rc1.horaSalida ASC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');

                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 5) Numero reserves parking
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesParking') {
                $data = array();
                $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
                        FROM reserves_parking as r
                        WHERE r.checkIn = 1");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');

                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 6) Reserves completades
            elseif (isset($_GET['type']) && $_GET['type'] == 'completades') {
                $data = array();
                $stmt = $conn->prepare("SELECT rc1.idReserva,
                        rc1.fechaReserva,
                        rc1.firstName AS 'clientNom',
                        rc1.lastName AS 'clientCognom',
                        rc1.tipo,
                        rc1.id,
                        rc1.importe,
                        c.nombre
                        FROM reserves_parking AS rc1
                        LEFT JOIN reservas_buscadores AS b ON rc1.buscadores = b.id
                        LEFT JOIN usuaris AS c ON rc1.idClient = c.id
                        WHERE rc1.checkOut = 2
                        GROUP BY rc1.id
                        ORDER BY rc1.id DESC");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');

                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
            }
            // 7) Numero reserves completades
            elseif (isset($_GET['type']) && $_GET['type'] == 'numReservesCompletades') {
                $data = array();
                $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
                        FROM reserves_parking as r
                        WHERE r.checkOut = 2");
                $stmt->execute();
                if ($stmt->rowCount() === 0) echo json_encode(['message' => 'No rows']);
                else {
                    while ($users = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        $data[] = $users;
                    }
                    // Establecer el encabezado de respuesta a JSON
                    header('Content-Type: application/json');

                    // Devolver los datos en formato JSON
                    echo json_encode($data);
                }
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
