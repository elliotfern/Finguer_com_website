<?php

declare(strict_types=1);

use Ramsey\Uuid\Uuid;

requireMethod('GET');

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}



/** @var PDO $conn */

$sqlSelect = "
    SELECT id, email
    FROM usuarios
    WHERE uuid IS NULL
";

$stmt = $conn->prepare($sqlSelect);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$usuarios) {
    echo "No hay usuarios sin uuid.\n";
    return;
}

$sqlUpdate = "
    UPDATE usuarios
    SET uuid = :uuid
    WHERE id = :id
";

$stmtUp = $conn->prepare($sqlUpdate);

$conn->beginTransaction();

try {
    foreach ($usuarios as $u) {
        $uuid = Uuid::uuid7();

        $stmtUp->bindValue(':uuid', $uuid->getBytes(), PDO::PARAM_LOB); // BINARY(16)
        $stmtUp->bindValue(':id', (int)$u['id'], PDO::PARAM_INT);
        $stmtUp->execute();

        echo "Usuario {$u['id']} → {$uuid->toString()}\n";
    }

    $conn->commit();
} catch (Throwable $e) {
    $conn->rollBack();
    throw $e;
}
