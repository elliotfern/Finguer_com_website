<?php
declare(strict_types=1);

requireMethod('POST');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */

if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}

$slug = $routeParams[0];

// =========================================================
// CREATE RESERVA ANUAL
// =========================================================
if ($slug === 'createReservaAnual') {

    try {
        $input = json_decode(file_get_contents("php://input"), true);

        // =========================
        // SANITIZACIÓN BÁSICA
        // =========================
        $usuarioUuid = isset($input['usuario_uuid']) ? trim((string)$input['usuario_uuid']) : null;
        $entradaPrevista = isset($input['entrada_prevista']) ? trim((string)$input['entrada_prevista']) : null;
        $salidaPrevista  = isset($input['salida_prevista']) ? trim((string)$input['salida_prevista']) : null;

        $vehiculo = isset($input['vehiculo']) ? trim((string)$input['vehiculo']) : null;
        $matricula = isset($input['matricula']) ? trim((string)$input['matricula']) : null;
        $vuelo = isset($input['vuelo']) ? trim((string)$input['vuelo']) : null;
        $notas = isset($input['notas']) ? trim((string)$input['notas']) : null;

        // =========================
        // VALIDACIONES OBLIGATORIAS
        // =========================
        if (!$usuarioUuid || !$entradaPrevista) {
            jsonResponse(vp2_err('Datos obligatorios faltantes', 'BAD_REQUEST'), 400);
        }

        // UUID HEX (32 chars)
        if (!preg_match('/^[a-fA-F0-9]{32}$/', $usuarioUuid)) {
            jsonResponse(vp2_err('UUID inválido', 'BAD_UUID'), 400);
        }

        // =========================
        // VALIDACIÓN FECHAS
        // =========================
        $entradaDT = DateTime::createFromFormat('Y-m-d H:i:s', $entradaPrevista);
        if (!$entradaDT) {
            jsonResponse(vp2_err('Formato entrada inválido', 'BAD_DATE'), 400);
        }

        $salidaDT = null;
        if (!empty($salidaPrevista)) {
            $salidaDT = DateTime::createFromFormat('Y-m-d H:i:s', $salidaPrevista);
            if (!$salidaDT) {
                jsonResponse(vp2_err('Formato salida inválido', 'BAD_DATE'), 400);
            }
        }

        // entrada < salida (si existe salida)
        if ($salidaDT && $salidaDT <= $entradaDT) {
            jsonResponse(vp2_err('La salida debe ser posterior a la entrada', 'BAD_DATE_RANGE'), 400);
        }

        // =========================
        // VALIDAR QUE USUARIO EXISTE
        // =========================
        $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM usuarios 
            WHERE uuid = UNHEX(:uuid)
        ");
        $check->execute([':uuid' => $usuarioUuid]);

        if ($check->fetchColumn() == 0) {
            jsonResponse(vp2_err('Usuario no existe', 'USER_NOT_FOUND'), 404);
        }

        // =========================
        // LOCALIZADOR
        // =========================
        $localizador = generarLocalizador($conn);

        // =========================
        // INSERT
        // =========================
        $query = "
            INSERT INTO parking_reservas (
                usuario_uuid,
                localizador,
                estado,
                estado_vehiculo,
                fecha_reserva,
                entrada_prevista,
                salida_prevista,
                subtotal_calculado,
                iva_calculado,
                total_calculado,
                vehiculo,
                matricula,
                personas,
                tipo,
                vuelo,
                notas,
                canal
            ) VALUES (
                UNHEX(:usuario_uuid),
                :localizador,
                'anual',
                'pendiente_entrada',
                NOW(),
                :entrada_prevista,
                :salida_prevista,
                NULL,
                NULL,
                NULL,
                :vehiculo,
                :matricula,
                NULL,
                '3',
                :vuelo,
                :notas,
                '5'
            )
        ";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ':usuario_uuid' => $usuarioUuid,
            ':localizador' => $localizador,
            ':entrada_prevista' => $entradaPrevista,
            ':salida_prevista' => $salidaPrevista,
            ':vehiculo' => $vehiculo,
            ':matricula' => $matricula,
            ':vuelo' => $vuelo,
            ':notas' => $notas
        ]);

        jsonResponse(vp2_ok('Reserva creada correctamente', [
            'localizador' => $localizador
        ]), 200);

    } catch (Throwable $e) {
        jsonResponse(vp2_err('Error servidor', 'SERVER_ERROR', [
            'details' => $e->getMessage()
        ]), 500);
    }
}