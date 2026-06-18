<?php
declare(strict_types=1);

requireMethod('PUT');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'), 500);
}


/* =========================
   Router POST por type
========================= */
$slug = $routeParams[0];

if ($slug === 'updateReservaAnual') {

    try {
        $input = json_decode(file_get_contents("php://input"), true);

        // =========================
        // SANITIZACIÓN
        // =========================
        $localizador = isset($input['localizador']) ? trim((string)$input['localizador']) : null;

        $entradaPrevista = isset($input['entrada_prevista']) ? trim((string)$input['entrada_prevista']) : null;
        $salidaPrevista  = isset($input['salida_prevista']) ? trim((string)$input['salida_prevista']) : null;

        $vehiculo = isset($input['vehiculo']) ? trim((string)$input['vehiculo']) : null;
        $matricula = isset($input['matricula']) ? trim((string)$input['matricula']) : null;
        $vuelo = isset($input['vuelo']) ? trim((string)$input['vuelo']) : null;
        $notas = isset($input['notas']) ? trim((string)$input['notas']) : null;

        // =========================
        // VALIDACIONES BÁSICAS
        // =========================
        if (!$localizador) {
            jsonResponse(vp2_err('Localizador requerido', 'BAD_REQUEST'), 400);
        }

        // localizador seguridad básica
        if (strlen($localizador) > 50) {
            jsonResponse(vp2_err('Localizador inválido', 'BAD_LOCALIZADOR'), 400);
        }

        // =========================
        // VALIDACIÓN FECHAS
        // =========================
        if (!$entradaPrevista) {
            jsonResponse(vp2_err('Entrada prevista obligatoria', 'BAD_REQUEST'), 400);
        }

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

        // coherencia lógica
        if ($salidaDT && $salidaDT <= $entradaDT) {
            jsonResponse(vp2_err('La salida debe ser posterior a la entrada', 'BAD_DATE_RANGE'), 400);
        }

        // =========================
        // VALIDAR QUE EXISTE RESERVA
        // =========================
        $check = $conn->prepare("
            SELECT COUNT(*) 
            FROM parking_reservas 
            WHERE localizador = :localizador 
              AND estado = 'anual'
        ");
        $check->execute([
            ':localizador' => $localizador
        ]);

        if ($check->fetchColumn() == 0) {
            jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
        }

        // =========================
        // UPDATE
        // =========================
        $query = "
            UPDATE parking_reservas
            SET
                entrada_prevista = :entrada_prevista,
                salida_prevista = :salida_prevista,
                vehiculo = :vehiculo,
                matricula = :matricula,
                vuelo = :vuelo,
                notas = :notas,
                updated_at = NOW()
            WHERE localizador = :localizador
              AND estado = 'anual'
        ";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ':entrada_prevista' => $entradaPrevista,
            ':salida_prevista' => $salidaPrevista,
            ':vehiculo' => $vehiculo,
            ':matricula' => $matricula,
            ':vuelo' => $vuelo,
            ':notas' => $notas,
            ':localizador' => $localizador
        ]);

        jsonResponse(vp2_ok('Reserva actualizada correctamente', []), 200);

    } catch (Throwable $e) {
        jsonResponse(vp2_err('Error servidor', 'SERVER_ERROR', [
            'details' => $e->getMessage()
        ]), 500);
    }
}