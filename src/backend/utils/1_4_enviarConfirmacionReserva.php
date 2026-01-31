<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarConfirmacionReserva(PDO $conn, int $reservaId, array $opts = []): array
{
    $opts = array_merge([
        'force' => false,                 // intranet: true => reenviar siempre
        'registrar_log' => true,          // si quieres desactivar logs
        'tipo_log' => 'confirmacion_reserva'
    ], $opts);

    if ($reservaId <= 0) {
        return vp2_err('ID de reserva no válido.', 'RESERVA_ID_INVALID');
    }

    // 1) Cargar reserva + usuario
    $sql = "
        SELECT
            pr.id,
            pr.localizador,
            pr.estado,
            pr.fecha_reserva,
            pr.entrada_prevista,
            pr.salida_prevista,
            pr.tipo,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.total_calculado,

            u.email,
            u.nombre,
            u.telefono
        FROM parking_reservas pr
        LEFT JOIN usuarios u
          ON pr.usuario_uuid = u.uuid
        WHERE pr.id = :id
        LIMIT 1
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $reservaId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return vp2_err('Reserva no encontrada.', 'RESERVA_NOT_FOUND');
    }

    // 2) Solo enviar si está pagada
    $estado = (string)($row['estado'] ?? '');
    if ($estado !== 'pagada') {
        return vp2_err('La reserva no está pagada. No se envía confirmación.', 'RESERVA_NOT_PAID', [
            'data' => ['estado' => $estado],
        ]);
    }

    $email  = trim((string)($row['email'] ?? ''));
    $nombre = trim((string)($row['nombre'] ?? ''));
    if ($email === '') {
        return vp2_err('El cliente no tiene email.', 'CLIENTE_EMAIL_EMPTY');
    }

    $tipoLog = (string)$opts['tipo_log'];

    // 3) Modo CRON: si ya hay "enviada", no reenviar
    if (empty($opts['force'])) {
        $stmtPrev = $conn->prepare("
            SELECT id, created_at
            FROM reservas_notificaciones
            WHERE reserva_id = :rid
              AND tipo = :tipo
              AND estado = 'enviada'
            ORDER BY id DESC
            LIMIT 1
        ");
        $stmtPrev->execute([
            ':rid'  => $reservaId,
            ':tipo' => $tipoLog,
        ]);
        $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

        if ($prev) {
            // log de omisión (opcional)
            if (!empty($opts['registrar_log'])) {
                $stmtIns = $conn->prepare("
                    INSERT INTO reservas_notificaciones
                    (reserva_id, tipo, estado, email, payload_json, error_text)
                    VALUES
                    (:rid, :tipo, 'omitida', :email, :payload, NULL)
                ");
                $stmtIns->execute([
                    ':rid'    => $reservaId,
                    ':tipo'   => $tipoLog,
                    ':email'  => $email,
                    ':payload' => json_encode([
                        'motivo' => 'ya_enviada',
                        'notificacion_id' => (int)$prev['id'],
                        'notificacion_fecha' => $prev['created_at'],
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            }

            return vp2_ok('Confirmación ya enviada previamente (modo CRON: no se reenvía).', [
                'reserva_id' => $reservaId,
                'email'      => $email,
                'omitida'    => true,
                'previo'     => $prev,
            ]);
        }
    }

    // 4) Preparar datos email
    $localizador = (string)($row['localizador'] ?? '');
    $entrada = (string)($row['entrada_prevista'] ?? '');
    $salida  = (string)($row['salida_prevista'] ?? '');

    $fechaEntrada = $entrada ? date('d-m-Y', strtotime($entrada)) : '';
    $horaEntrada  = $entrada ? date('H:i',   strtotime($entrada)) : '';
    $fechaSalida  = $salida  ? date('d-m-Y', strtotime($salida))  : '';
    $horaSalida   = $salida  ? date('H:i',   strtotime($salida))  : '';

    $tipoSrv = (int)($row['tipo'] ?? 1);
    $tipoTxt = ($tipoSrv === 2) ? 'Gold Finguer Class' : 'Finguer Class';

    $importe = (float)($row['total_calculado'] ?? 0);

    // Limpieza (opcional, igual que tu función antigua)
    $limpiezaTxt = 'Sin servicio de limpieza';
    $stmtLimp = $conn->prepare("
        SELECT s.codigo, s.nombre
        FROM parking_reservas_servicios prs
        JOIN parking_servicios_catalogo s
          ON s.id = prs.servicio_id
        WHERE prs.reserva_id = :rid
          AND s.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')
        ORDER BY prs.id ASC
        LIMIT 1
    ");
    $stmtLimp->execute([':rid' => $reservaId]);
    $l = $stmtLimp->fetch(PDO::FETCH_ASSOC);
    if ($l) {
        switch ($l['codigo']) {
            case 'LIMPIEZA_EXT':
                $limpiezaTxt = 'Servicio de limpieza exterior';
                break;
            case 'LIMPIEZA_EXT_INT':
                $limpiezaTxt = 'Servicio de lavado exterior + aspirado tapicería interior';
                break;
            case 'LIMPIEZA_PRO':
                $limpiezaTxt = 'Limpieza PRO';
                break;
            default:
                $limpiezaTxt = (string)$l['nombre'];
                break;
        }
    }

    $payload = [
        'force' => (bool)$opts['force'],
        'localizador' => $localizador,
        'email' => $email,
        'nombre' => $nombre,
        'entrada' => $entrada,
        'salida' => $salida,
        'tipo' => $tipoTxt,
        'limpieza' => $limpiezaTxt,
        'importe' => $importe,
    ];

    // 5) Config SMTP
    $brevoApi = (string)($_ENV['BREVO_API'] ?? '');
    if ($brevoApi === '') {
        return vp2_err('BREVO_API no configurada.', 'BREVO_API_MISSING');
    }

    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

    try {
        $mail = new PHPMailer(true);
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '7a0605001@smtp-brevo.com';
        $mail->Password   = $brevoApi;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('hello@finguer.com', 'Finguer.com');
        $mail->addAddress($email, $nombre ?: $email);
        $mail->addBCC('hello@finguer.com', 'Finguer.com');
        $mail->addBCC('elliot@hispantic.com', 'Elliot');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Confirmación de su reserva en Finguer.com';

        $mail->Body = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmación de Reserva en Finguer.com</title>
            </head>

            <body style="font-family: Arial, sans-serif; background-color:#f4f4f4; color:#333; margin:0; padding:0;">
            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600"
                    style="border-collapse:collapse; background-color:#ffffff;">
                
                <!-- Logo (fondo blanco) -->
                <tr>
                <td align="center" style="padding: 20px 0; background-color:#ffffff;">
                    <img src="https://finguer.com/public/img/logo-header-sticky.png"
                        alt="Finguer"
                        width="220"
                        style="display:block; border:0; outline:none; text-decoration:none;">
                </td>
                </tr>

                <!-- Título (azul) -->
                <tr>
                <td align="center" bgcolor="#007bff" style="padding: 22px 20px;">
                    <h1 style="color:#ffffff; margin:0; font-size:20px; line-height:1.3;">
                    Confirmación de Reserva de Parking en Finguer.com
                    </h1>
                </td>
                </tr>

                <!-- Contenido -->
                <tr>
                <td style="padding: 30px 30px;">
                    <p>Estimado/a ' . htmlspecialchars($nombre ?: '') . ',</p>
                    <p>Su reserva de parking ha sido confirmada.</p>

                    <ul style="padding-left:18px; margin: 14px 0;">
                    <li><strong>Localizador:</strong> ' . htmlspecialchars($localizador) . '</li>
                    <li><strong>Tipo de servicio:</strong> ' . htmlspecialchars($tipoTxt) . '</li>
                    <li><strong>Limpieza:</strong> ' . htmlspecialchars($limpiezaTxt) . '</li>
                    <li><strong>Entrada:</strong> ' . htmlspecialchars($fechaEntrada) . ' - ' . htmlspecialchars($horaEntrada) . '</li>
                    <li><strong>Salida:</strong> ' . htmlspecialchars($fechaSalida) . ' - ' . htmlspecialchars($horaSalida) . '</li>
                    <li><strong>Precio (IVA incluido):</strong> ' . number_format($importe, 2, ',', '') . ' €</li>
                    </li><strong>Lugar de Parking</strong>: Carrer de l\'Alt Camp, 9, 08830 Sant Boi de Llobregat, (Barcelona) España</li>
                    </ul>

                    <p>Por favor, asegúrese de llegar a tiempo y tener su reserva a mano para su presentación.</p>  

                    <p>Si tiene alguna pregunta o necesita más información, no dude en ponerse en contacto con nosotros.</p>

                    <p>Gracias por elegir nuestro servicio.</p>
                    <p style="margin-bottom:0;">AUTO GESTIO FERCAR S.L - Finguer.com</p>
                </td>
                </tr>

                <!-- Footer -->
                <tr>
                <td align="center" bgcolor="#007bff" style="padding: 16px 20px;">
                    <p style="color:#ffffff; margin:0; font-size:12px; line-height:1.4;">
                    Este correo electrónico fue enviado automáticamente. Por favor no respondas a este mensaje.
                    </p>
                </td>
                </tr>

            </table>
            </body>
            </html>';

        $mail->send();

        if (!empty($opts['registrar_log'])) {
            $stmtIns = $conn->prepare("
                INSERT INTO reservas_notificaciones
                (reserva_id, tipo, estado, email, payload_json, error_text)
                VALUES
                (:rid, :tipo, 'enviada', :email, :payload, NULL)
            ");
            $stmtIns->execute([
                ':rid'     => $reservaId,
                ':tipo'    => $tipoLog,
                ':email'   => $email,
                ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            ]);
        }

        return vp2_ok('Confirmación enviada correctamente.', [
            'reserva_id' => $reservaId,
            'email'      => $email,
            'force'      => (bool)$opts['force'],
        ]);
    } catch (\Throwable $e) {

        if (!empty($opts['registrar_log'])) {
            $stmtErr = $conn->prepare("
                INSERT INTO reservas_notificaciones
                (reserva_id, tipo, estado, email, payload_json, error_text)
                VALUES
                (:rid, :tipo, 'error', :email, :payload, :err)
            ");
            $stmtErr->execute([
                ':rid'     => $reservaId,
                ':tipo'    => $tipoLog,
                ':email'   => $email,
                ':payload' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                ':err'     => $e->getMessage(),
            ]);
        }

        return vp2_err('Error enviando confirmación.', 'EMAIL_SEND_ERROR', [
            'data' => ['error' => $e->getMessage()],
        ]);
    }
}
