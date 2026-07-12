<?php
declare(strict_types=1);

use App\Infrastructure\EntryPoint\Http\Usuario\ListarUsuariosController;
use App\Infrastructure\EntryPoint\Http\Usuario\ObtenerUsuarioController;

// Solo admin (por ahora)
$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    jsonResponse(vp2_err('No autoritzat', 'FORBIDDEN'), 403);
}

$type = (string) ($_GET['type'] ?? '');

if ($type === 'usuarios-list') {
    ListarUsuariosController::handle();
    exit();
}

if ($type === 'usuarios-get') {
    ObtenerUsuarioController::handle();
    exit();
}

try {
    // =========================================================
    // type=clienteAnual  (detalle cliente anual por uuid)
    // GET ?type=clienteAnual&uuid=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
    // =========================================================
    if ($type === 'clienteAnual') {
        $uuidStr = isset($_GET['uuid']) ? trim((string) $_GET['uuid']) : '';
        if ($uuidStr === '') {
            jsonResponse(vp2_err('Falta parámetro uuid', 'BAD_UUID'), 400);
        }

        try {
            $uuidBin = uuid_bin_from_string($uuidStr);
        } catch (Throwable $e) {
            jsonResponse(vp2_err('UUID inválido', 'BAD_UUID'), 400);
        }

        // =========================
        // USUARIO
        // =========================
        $sqlUser = "
        SELECT *
        FROM usuarios
        WHERE uuid = :uuid
        LIMIT 1
    ";

        $stmt = $conn->prepare($sqlUser);
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            jsonResponse(vp2_err('Usuario no encontrado', 'NOT_FOUND'), 404);
        }

        // =========================
        // ABONO PERFIL
        // =========================
        $sqlPerfil = "
        SELECT *
        FROM usuarios_perfil
        WHERE usuario_uuid = :uuid
        LIMIT 1
    ";

        $stmt4 = $conn->prepare($sqlPerfil);
        $stmt4->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt4->execute();

        $perfil = $stmt4->fetch(PDO::FETCH_ASSOC);

        // =========================
        // ABONO ANUAL
        // =========================
        $sqlAbono = "
        SELECT *
        FROM usuarios_abonos
        WHERE usuario_uuid = :uuid
        LIMIT 1
    ";

        $stmt2 = $conn->prepare($sqlAbono);
        $stmt2->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt2->execute();

        $abono = $stmt2->fetch(PDO::FETCH_ASSOC);

        // =========================
        // RESERVAS USADAS
        // =========================
        $sqlReservas = "
        SELECT COUNT(*)
        FROM parking_reservas
        WHERE usuario_uuid = :uuid
    ";

        $stmt3 = $conn->prepare($sqlReservas);
        $stmt3->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt3->execute();

        $reservasUsadas = (int) $stmt3->fetchColumn();

        // =========================
        // RESPONSE UNIFICADO PARA TYPESCRIPT
        // =========================
        jsonResponse(
            vp2_ok('OK', [
                'uuid' => uuid_string_from_bin($user['uuid']),
                'nombre' => (string) $perfil['nombre'],
                'email' => (string) $user['email'],
                'empresa' => $perfil['empresa'] ?? null,
                'nif' => $perfil['nif'] ?? null,
                'direccion' => $perfil['direccion'] ?? null,
                'ciudad' => $perfil['ciudad'] ?? null,
                'codigo_postal' => $perfil['codigo_postal'] ?? null,
                'pais' => $perfil['pais'] ?? null,
                'telefono' => $perfil['telefono'] ?? null,
                'tipo_rol' => (string) $user['tipo_rol'],
                'locale' => (string) $user['locale'],
                'createdAt' => $user['created_at'] ?? null,
                'updatedAt' => $user['updated_at'] ?? null,

                // 👇 todos los campos de $abono protegidos
                'fecha_inicio' => $abono['fecha_inicio'] ?? null,
                'fecha_fin' => $abono['fecha_fin'] ?? null,
                'limite_reservas' => $abono
                    ? (int) $abono['limite_reservas']
                    : null,
                'vehiculo' => $abono['vehiculo'] ?? null,
                'matricula' => $abono['matricula'] ?? null,
                'observaciones' => $abono['observaciones'] ?? null,
                'estado' => $abono['estado'] ?? null,

                'usadas' => $reservasUsadas,
                'disponibles' => $abono
                    ? max(0, (int) $abono['limite_reservas'] - $reservasUsadas)
                    : null,
            ]),
        );
    }
    // Si llega aquí, type no válido
    jsonResponse(
        vp2_err('type inválido', 'BAD_TYPE', [
            'allowed' => ['list', 'get'],
        ]),
        400,
    );
} catch (Throwable $e) {
    jsonResponse(
        vp2_err('Error interno', 'SERVER_ERROR', [
            'details' => $e->getMessage(),
        ]),
        500,
    );
}
