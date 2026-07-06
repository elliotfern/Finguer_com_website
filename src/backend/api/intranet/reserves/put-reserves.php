<?php
declare(strict_types=1);

requireMethod('PUT');
requireAuthTokenCookie();

global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    jsonResponse(
        vp2_err('DB connection not available', 'DB_NOT_AVAILABLE'),
        500,
    );
}

/* =========================
   Router POST por type
=========================   */
$slug = $routeParams[0];

if ($slug === 'updateReservaAnual') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // =========================
        // INPUT RAW FORM
        // =========================
        $localizador = isset($input['localizador'])
            ? trim((string) $input['localizador'])
            : null;

        $diaEntrada = $input['diaEntrada'] ?? null;
        $horaEntrada = $input['horaEntrada'] ?? null;

        $diaSalida = $input['diaSalida'] ?? null;
        $horaSalida = $input['horaSalida'] ?? null;

        $vehiculo = isset($input['vehiculo'])
            ? trim((string) $input['vehiculo'])
            : null;
        $matricula = isset($input['matricula'])
            ? trim((string) $input['matricula'])
            : null;
        $vuelo = isset($input['vuelo']) ? trim((string) $input['vuelo']) : null;
        $notas = isset($input['notes']) ? trim((string) $input['notes']) : null;

        // =========================
        // VALIDACIONES BÁSICAS
        // =========================
        if (!$localizador) {
            jsonResponse(vp2_err('Localizador requerido', 'BAD_REQUEST'), 400);
        }

        if (!$diaEntrada || !$horaEntrada) {
            jsonResponse(
                vp2_err('Fecha y hora de entrada obligatorias', 'BAD_DATE'),
                400,
            );
        }

        // =========================
        // CONSTRUIR DATETIME
        // =========================
        $entradaStr = $diaEntrada . ' ' . $horaEntrada . ':00';
        $entradaDT = DateTime::createFromFormat('Y-m-d H:i:s', $entradaStr);

        if (!$entradaDT) {
            jsonResponse(vp2_err('Entrada inválida', 'BAD_DATE'), 400);
        }

        $salidaDT = null;

        if ($diaSalida && $horaSalida) {
            $salidaStr = $diaSalida . ' ' . $horaSalida . ':00';
            $salidaDT = DateTime::createFromFormat('Y-m-d H:i:s', $salidaStr);

            if (!$salidaDT) {
                jsonResponse(vp2_err('Salida inválida', 'BAD_DATE'), 400);
            }

            if ($salidaDT <= $entradaDT) {
                jsonResponse(
                    vp2_err(
                        'La salida debe ser posterior a la entrada',
                        'BAD_DATE_RANGE',
                    ),
                    400,
                );
            }
        }

        // =========================
        // VALIDAR EXISTENCIA
        // =========================
        $check = $conn->prepare("
            SELECT COUNT(*)
            FROM parking_reservas
            WHERE localizador = :localizador
              AND estado = 'anual'
        ");

        $check->execute([
            ':localizador' => $localizador,
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
            ':entrada_prevista' => $entradaDT->format('Y-m-d H:i:s'),
            ':salida_prevista' => $salidaDT
                ? $salidaDT->format('Y-m-d H:i:s')
                : null,
            ':vehiculo' => $vehiculo,
            ':matricula' => $matricula,
            ':vuelo' => $vuelo,
            ':notas' => $notas,
            ':localizador' => $localizador,
        ]);

        jsonResponse(vp2_ok('Reserva actualizada correctamente', []), 200);
    } catch (Throwable $e) {
        jsonResponse(
            vp2_err('Error servidor', 'SERVER_ERROR', [
                'details' => $e->getMessage(),
            ]),
            500,
        );
    }
} elseif ($slug === 'updateReserva') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        // =========================
        // INPUT RAW FORM
        // =========================
        $id = isset($input['id']) ? (int) $input['id'] : 0;

        $estado = isset($input['estado'])
            ? trim((string) $input['estado'])
            : null;
        $estadoVehiculo = isset($input['estado_vehiculo'])
            ? trim((string) $input['estado_vehiculo'])
            : null;
        $tipo = isset($input['tipo']) ? trim((string) $input['tipo']) : null;
        $canal = isset($input['canal']) ? trim((string) $input['canal']) : null;

        $entradaPrevista = isset($input['entrada_prevista'])
            ? trim((string) $input['entrada_prevista'])
            : null;
        $salidaPrevista = isset($input['salida_prevista'])
            ? trim((string) $input['salida_prevista'])
            : null;

        $vehiculo = isset($input['vehiculo'])
            ? trim((string) $input['vehiculo'])
            : null;
        $matricula = isset($input['matricula'])
            ? trim((string) $input['matricula'])
            : null;
        $personas =
            isset($input['personas']) && $input['personas'] !== ''
                ? (int) $input['personas']
                : null;
        $vuelo = isset($input['vuelo']) ? trim((string) $input['vuelo']) : null;
        $notas = isset($input['notas']) ? trim((string) $input['notas']) : null;

        // =========================
        // VALIDACIONES BÁSICAS
        // =========================
        if ($id <= 0) {
            jsonResponse(
                vp2_err('ID de reserva requerido', 'BAD_REQUEST'),
                400,
            );
        }

        $estadosPermitidos = [
            'pendiente',
            'procesando_pago',
            'pago_oficina',
            'pagada',
            'cancelada',
            'anual',
        ];
        if (!$estado || !in_array($estado, $estadosPermitidos, true)) {
            jsonResponse(
                vp2_err('Estat de reserva inválido', 'BAD_ESTADO'),
                400,
            );
        }

        $estadosVehiculoPermitidos = ['pendiente_entrada', 'dentro', 'salido'];
        if (
            !$estadoVehiculo ||
            !in_array($estadoVehiculo, $estadosVehiculoPermitidos, true)
        ) {
            jsonResponse(
                vp2_err('Estat de vehicle inválido', 'BAD_ESTADO_VEHICULO'),
                400,
            );
        }

        if (!$entradaPrevista || !$salidaPrevista) {
            jsonResponse(
                vp2_err('Entrada i sortida previstes obligatòries', 'BAD_DATE'),
                400,
            );
        }

        // =========================
        // CONSTRUIR DATETIME
        // (datetime-local envía "Y-m-d\TH:i", sin segundos)
        // =========================
        $entradaDT =
            DateTime::createFromFormat('Y-m-d\TH:i', $entradaPrevista) ?:
            DateTime::createFromFormat('Y-m-d H:i:s', $entradaPrevista);
        $salidaDT =
            DateTime::createFromFormat('Y-m-d\TH:i', $salidaPrevista) ?:
            DateTime::createFromFormat('Y-m-d H:i:s', $salidaPrevista);

        if (!$entradaDT) {
            jsonResponse(vp2_err('Entrada prevista inválida', 'BAD_DATE'), 400);
        }
        if (!$salidaDT) {
            jsonResponse(vp2_err('Sortida prevista inválida', 'BAD_DATE'), 400);
        }
        if ($salidaDT <= $entradaDT) {
            jsonResponse(
                vp2_err(
                    'La sortida ha de ser posterior a l\'entrada',
                    'BAD_DATE_RANGE',
                ),
                400,
            );
        }

        // =========================
        // VALIDAR EXISTENCIA
        // =========================
        $check = $conn->prepare("
            SELECT COUNT(*)
            FROM parking_reservas
            WHERE id = :id
        ");
        $check->execute([':id' => $id]);

        if ($check->fetchColumn() == 0) {
            jsonResponse(vp2_err('Reserva no encontrada', 'NOT_FOUND'), 404);
        }

        // =========================
        // UPDATE
        // =========================
        $query = "
            UPDATE parking_reservas
            SET
                estado = :estado,
                estado_vehiculo = :estado_vehiculo,
                tipo = :tipo,
                canal = :canal,
                entrada_prevista = :entrada_prevista,
                salida_prevista = :salida_prevista,
                vehiculo = :vehiculo,
                matricula = :matricula,
                personas = :personas,
                vuelo = :vuelo,
                notas = :notas
            WHERE id = :id
        ";

        $stmt = $conn->prepare($query);

        $stmt->execute([
            ':estado' => $estado,
            ':estado_vehiculo' => $estadoVehiculo,
            ':tipo' => $tipo,
            ':canal' => $canal,
            ':entrada_prevista' => $entradaDT->format('Y-m-d H:i:s'),
            ':salida_prevista' => $salidaDT->format('Y-m-d H:i:s'),
            ':vehiculo' => $vehiculo,
            ':matricula' => $matricula,
            ':personas' => $personas,
            ':vuelo' => $vuelo,
            ':notas' => $notas,
            ':id' => $id,
        ]);

        jsonResponse(vp2_ok('Reserva actualizada correctamente', []), 200);
    } catch (Throwable $e) {
        jsonResponse(
            vp2_err('Error servidor', 'SERVER_ERROR', [
                'details' => $e->getMessage(),
            ]),
            500,
        );
    }
}
