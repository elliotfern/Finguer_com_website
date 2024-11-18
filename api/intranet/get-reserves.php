<?php
global $conn;

// 1) Llistat reserves
// ruta GET => "/api/reserves/get/?type=pendents"
if (isset($_GET['type']) && $_GET['type'] == 'pendents' ) {
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
    u.telefono AS tel
    FROM reserves_parking AS rc1
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    left JOIN usuaris AS u ON rc1.idClient = u.id
    WHERE rc1.checkIn = 5
    ORDER BY rc1.diaEntrada ASC, rc1.horaEntrada ASC");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

    // 2) Llistat de reserves totals
// ruta GET => "/api/reserves/get/?type=reserves"
} elseif (isset($_GET['type']) && $_GET['type'] == 'reserves' ) {
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
    u.telefono AS tel
    FROM reserves_parking AS rc1
    left JOIN usuaris AS u ON rc1.idClient = u.id
    WHERE rc1.checkIn = 5 OR rc1.checkIn = 1
    ORDER BY rc1.id desc;");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);


// 2) Numero reserves pendents
// ruta GET => "/api/reserves/get/?type=numReservesPendents"
} elseif  (isset($_GET['type']) && $_GET['type'] == 'numReservesPendents' ) {
    $data = array();
    $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
    FROM reserves_parking as r
    WHERE r.checkIn = 5");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

// 3) Reserves al parking
// ruta GET => "/api/reserves/get/?type=parking"
} elseif  (isset($_GET['type']) && $_GET['type'] == 'parking' ) {
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
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    left JOIN usuaris AS u ON rc1.idClient = u.id
    WHERE rc1.checkIn = 1
    GROUP BY rc1.id
    ORDER BY rc1.diaSalida ASC, rc1.horaSalida  ASC");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

// 4) Numero reserves parking
// ruta GET => "/api/reserves/get/?type=numReservesParking"
} elseif  (isset($_GET['type']) && $_GET['type'] == 'numReservesParking' ) {
    $data = array();
    $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
    FROM reserves_parking as r
    WHERE r.checkIn = 1");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

// 5) Reserves completades
// ruta GET => "/api/reserves/get/?type=completades"
} elseif  (isset($_GET['type']) && $_GET['type'] == 'completades' ) {
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
    left join reservas_buscadores AS b ON rc1.buscadores = b.id
    LEFT JOIN usuaris AS c ON rc1.idClient = c.id
    WHERE rc1.checkOut = 2
    GROUP BY rc1.id
    ORDER BY rc1.id DESC");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

// 6) Numero reserves completades
// ruta GET => "/api/reserves/get/?type=numReservesCompletades"
} elseif  (isset($_GET['type']) && $_GET['type'] == 'numReservesCompletades' ) {
    $data = array();
    $stmt = $conn->prepare("SELECT COUNT(r.idReserva) AS numero
    FROM reserves_parking as r
    WHERE r.checkOut = 2");
    $stmt->execute();
        if($stmt->rowCount() === 0) echo ('No rows');
            while($users = $stmt->fetch(PDO::FETCH_ASSOC) ){
                $data[] = $users;
        }
    echo json_encode($data);

}