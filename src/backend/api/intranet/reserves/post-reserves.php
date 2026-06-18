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
        // INPUT RAW (form real)
        // =========================
        $usuarioUuid = isset($input['usuario_uuid_hidden']) ? trim((string)$input['usuario_uuid_hidden']) : null;

        $diaEntrada = $input['diaEntrada'] ?? null;
        $horaEntrada = $input['horaEntrada'] ?? null;

        $diaSalida = $input['diaSalida'] ?? null;
        $horaSalida = $input['horaSalida'] ?? null;

        $vehiculo = isset($input['vehiculo']) ? trim((string)$input['vehiculo']) : null;
        $matricula = isset($input['matricula']) ? trim((string)$input['matricula']) : null;
        $vuelo = isset($input['vuelo']) ? trim((string)$input['vuelo']) : null;
        $notas = isset($input['notes']) ? trim((string)$input['notes']) : null;

        // =========================
        // VALIDACIONES BÁSICAS
        // =========================
        if (!$usuarioUuid) {
            jsonResponse(vp2_err('Usuario obligatorio', 'BAD_REQUEST'), 400);
        }

        if (!preg_match('/^[a-fA-F0-9]{32}$/', $usuarioUuid)) {
            jsonResponse(vp2_err('UUID inválido', 'BAD_UUID'), 400);
        }

        if (!$diaEntrada || !$horaEntrada) {
            jsonResponse(vp2_err('Entrada obligatoria', 'BAD_DATE'), 400);
        }

        // =========================
        // CONSTRUIR DATETIME
        // =========================
        $entradaStr = $diaEntrada . ' ' . $horaEntrada . ':00';
        $entradaDT = DateTime::createFromFormat('Y-m-d H:i:s', $entradaStr);

        if (!$entradaDT) {
            jsonResponse(vp2_err('Formato entrada inválido', 'BAD_DATE'), 400);
        }

        $salidaDT = null;

        if ($diaSalida && $horaSalida) {
            $salidaStr = $diaSalida . ' ' . $horaSalida . ':00';
            $salidaDT = DateTime::createFromFormat('Y-m-d H:i:s', $salidaStr);

            if (!$salidaDT) {
                jsonResponse(vp2_err('Formato salida inválido', 'BAD_DATE'), 400);
            }

            if ($salidaDT <= $entradaDT) {
                jsonResponse(vp2_err('La salida debe ser posterior a la entrada', 'BAD_DATE_RANGE'), 400);
            }
        }

        // =========================
        // VALIDAR USUARIO EXISTE
        // =========================
        $checkUser = $conn->prepare("
            SELECT COUNT(*) 
            FROM usuarios 
            WHERE uuid = UNHEX(:uuid)
        ");

        $checkUser->execute([
            ':uuid' => $usuarioUuid
        ]);

        if ($checkUser->fetchColumn() == 0) {
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
            ':entrada_prevista' => $entradaDT->format('Y-m-d H:i:s'),
            ':salida_prevista' => $salidaDT ? $salidaDT->format('Y-m-d H:i:s') : null,
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