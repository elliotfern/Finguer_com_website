<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

/**
 * Envía la factura por email (adjunta el PDF).
 * - Reutiliza generarFacturaPdf($idFactura, ['mode' => 'F'])
 * - Idempotente: si force=false, intenta no “re-enviar” en modo cron (opcional)
 * - Devuelve array (ideal para JSON en endpoints y para CRON)
 */
function enviarFacturaPorEmail(PDO $conn, int $idFactura, array $opts = []): array
{
    $opts = array_merge([
        'origen'        => 'cron',   // 'cron' | 'intranet'
        'force_send'    => false,    // true => reenvía aunque ya se haya enviado (intranet)
        'force_pdf'     => false,    // true => regenera PDF aunque exista
        'base_dir'      => '/home/epgylzqu/finguer.com',
        'subdir'        => '/pdf/facturas',
        'from_email'    => 'web@finguer.com',
        'from_name'     => 'Finguer',
        'reply_to'      => null,     // ej: 'hello@finguer.com'
        'bcc'           => ['hello@finguer.com', 'elliot@hispantic.com'],
        'subject'       => 'Factura servicios Finguer.com',
        'body_html'     => null,     // si null, usa texto simple
        'body_text'     => 'Adjunto encontrarás el documento PDF con tu factura.',
        // Si quieres “no reenviar” en CRON porque ya se envió: pon a true y necesitas el check.
        'skip_if_already_sent' => false,
    ], $opts);

    if ($idFactura <= 0) {
        return vp2_err('ID de factura no válido.', 'FACTURA_ID_INVALID');
    }

    // 1) Cargar destinatario (mínimo)
    $stmt = $conn->prepare("
        SELECT
            f.id,
            f.numero,
            f.serie,
            u.email,
            u.nombre
        FROM facturas f
        JOIN usuarios u ON u.id = f.usuario_uuid
        WHERE f.id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $idFactura]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return vp2_err('No se encontró la factura.', 'FACTURA_NOT_FOUND', [
            'data' => ['idFactura' => $idFactura],
        ]);
    }

    $email = trim((string)($row['email'] ?? ''));
    $nombre = trim((string)($row['nombre'] ?? ''));
    $serie  = (string)($row['serie'] ?? '');
    $numero = (string)($row['numero'] ?? '');

    if ($email === '') {
        return vp2_err('La factura no tiene email de destinatario.', 'FACTURA_EMAIL_EMPTY', [
            'data' => ['idFactura' => $idFactura],
        ]);
    }

    // 2) (Opcional) Evitar reenvío automático en CRON
    if (!empty($opts['skip_if_already_sent']) && empty($opts['force_send'])) {
        // Si ya registras logs tipo 'envio_email', puedes comprobarlo aquí.
        // Ajusta tabla/estructura si es distinta.
        $chk = $conn->prepare("
            SELECT id
            FROM facturas_logs
            WHERE factura_id = :fid
              AND accion = 'envio_email'
            LIMIT 1
        ");
        $chk->execute([':fid' => $idFactura]);
        if ($chk->fetch(PDO::FETCH_ASSOC)) {
            return vp2_ok('Email ya fue enviado anteriormente (skip cron).', [
                'idFactura' => $idFactura,
                'email'     => $email,
                'skipped'   => true,
            ]);
        }
    }

    // 3) Generar (o reutilizar) el PDF en disco
    $pdfRes = generarFacturaPdf($idFactura, [
        'mode'     => 'F',
        'base_dir' => $opts['base_dir'],
        'subdir'   => $opts['subdir'],
        'force'    => (bool)$opts['force_pdf'],
    ]);

    if (($pdfRes['status'] ?? '') !== 'success') {
        return vp2_err('No se pudo generar el PDF para enviar.', 'FACTURA_PDF_ERROR', [
            'data' => [
                'idFactura' => $idFactura,
                'pdfRes'    => $pdfRes,
            ],
        ]);
    }

    $pdfPath = (string)($pdfRes['path'] ?? '');
    if ($pdfPath === '' || !file_exists($pdfPath)) {
        return vp2_err('PDF no encontrado en disco.', 'FACTURA_PDF_MISSING', [
            'data' => ['path' => $pdfPath],
        ]);
    }

    // 4) Enviar email
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

    $brevoApi = (string)($_ENV['BREVO_API'] ?? '');
    if ($brevoApi === '') {
        return vp2_err('Falta BREVO_API en configuración.', 'BREVO_API_MISSING');
    }

    try {
        $mail = new PHPMailer(true);
        $mail->CharSet = 'UTF-8';

        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '7a0605001@smtp-brevo.com';
        $mail->Password   = $brevoApi;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom($opts['from_email'], $opts['from_name']);
        if (!empty($opts['reply_to'])) {
            $mail->addReplyTo((string)$opts['reply_to']);
        }

        $mail->addAddress($email, $nombre !== '' ? $nombre : $email);

        // BCCs
        if (!empty($opts['bcc']) && is_array($opts['bcc'])) {
            foreach ($opts['bcc'] as $bcc) {
                $bcc = trim((string)$bcc);
                if ($bcc !== '') $mail->addBCC($bcc);
            }
        }

        // Adjuntar
        $safeName = sprintf('factura_%s_%s.pdf', $serie, $numero);
        $mail->addAttachment($pdfPath, $safeName);

        $mail->Subject = (string)$opts['subject'];

        if (!empty($opts['body_html'])) {
            $mail->isHTML(true);
            $mail->Body = (string)$opts['body_html'];
            $mail->AltBody = (string)$opts['body_text'];
        } else {
            $mail->isHTML(false);
            $mail->Body = (string)$opts['body_text'];
        }

        $mail->send();

        // Log OK
        $usuarioBackofficeId = ($opts['origen'] === 'intranet')
            ? getUsuarioBackofficeIdFromCookie()
            : null;

        registrarLogFactura($conn, $idFactura, $usuarioBackofficeId, 'envio_email', [
            'email'     => $email,
            'pdf'       => $pdfPath,
            'origen'    => $opts['origen'],
            'force_send' => (bool)$opts['force_send'],
        ]);

        return vp2_ok('Factura enviada correctamente por email.', [
            'idFactura' => $idFactura,
            'email'     => $email,
            'pdf'       => $pdfPath,
        ]);
    } catch (\Throwable $e) {
        $usuarioBackofficeId = ($opts['origen'] === 'intranet')
            ? getUsuarioBackofficeIdFromCookie()
            : null;

        // OJO: $mail puede no existir si peta antes → no uses $mail->ErrorInfo aquí.
        registrarLogFactura($conn, $idFactura, $usuarioBackofficeId, 'envio_email_error', [
            'email'  => $email,
            'origen' => $opts['origen'],
            'error'  => $e->getMessage(),
        ]);

        error_log('[FINGUER] Error enviando factura: ' . $e->getMessage());

        return vp2_err('Error enviando la factura por email.', 'FACTURA_EMAIL_ERROR', [
            'data' => ['error' => $e->getMessage()],
        ]);
    }
}
