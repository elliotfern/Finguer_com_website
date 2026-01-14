<?php
declare(strict_types=1);

/**
 * Devuelve el usuario_uuid (bin16) del usuario logueado.
 * Lanza Exception si no hay sesión válida.
 *
 * Fuentes soportadas (por orden):
 *  1) $_SESSION['usuario_uuid'] (string uuid con guiones o sin)
 *  2) $_SESSION['usuario_id'] (legacy int) -> lo resolvemos a uuid en BD
 */
function current_user_uuid_bin(PDO $pdo): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // si tu proyecto ya hace session_start() en otro sitio, puedes quitar esto
        session_start();
    }

    // 1) Nuevo: uuid en sesión
    if (!empty($_SESSION['usuario_uuid'])) {
        return uuid_bin_from_string((string)$_SESSION['usuario_uuid']);
    }

    // 2) Legacy: id en sesión => resolvemos a uuid
    if (!empty($_SESSION['usuario_id'])) {
        $usuarioId = (int)$_SESSION['usuario_id'];

        $stmt = $pdo->prepare("SELECT uuid FROM usuarios WHERE id = :id LIMIT 1");
        $stmt->execute([':id' => $usuarioId]);
        $bin = $stmt->fetchColumn();

        if ($bin === false || $bin === null || strlen((string)$bin) !== 16) {
            throw new RuntimeException("Sesión inválida: usuario no encontrado o uuid inválido (id=$usuarioId)");
        }

        // opcional pero MUY útil: guardar ya el uuid en sesión para próximas requests
        $_SESSION['usuario_uuid'] = uuid_string_from_bin((string)$bin);

        return (string)$bin;
    }

    throw new RuntimeException("No autenticado: falta usuario_uuid/usuario_id en sesión");
}

function usuario_uuid_exists(PDO $pdo, string $usuarioUuidBin): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM usuarios WHERE uuid = :u LIMIT 1");
    $stmt->bindValue(':u', $usuarioUuidBin, PDO::PARAM_LOB);
    $stmt->execute();
    return (bool)$stmt->fetchColumn();
}
