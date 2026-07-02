<?php

use App\Utils\Mailer;

function enviarFacturaPorEmail(
    PDO $conn,
    int $idFactura,
    array $opts = [],
): array {
    $BASE_DIR = $_ENV['APP_BASE_DIR'] ?? '/home/epgylzqu/finguer.com/public';

    $opts = array_merge(
        [
            'origen' => 'cron',
            'force_send' => false,
            'force_pdf' => false,
            'base_dir' => $BASE_DIR,
            'subdir' => '/pdf/facturas',
            'from_email' => 'web@finguer.com',
            'from_name' => 'Finguer',
            'reply_to' => null,
            'bcc' => [
                'hello@finguer.com' => 'Finguer.com',
                'elliot@hispantic.com' => 'Elliot',
            ],
            'subject' => 'Factura servicios Finguer.com',
            'body_html' => null,
            'body_text' =>
                'Adjunto encontrarás el documento PDF con tu factura.',
            'skip_if_already_sent' => false,
        ],
        $opts,
    );

    if ($idFactura <= 0) {
        return vp2_err('ID de factura no válido.', 'FACTURA_ID_INVALID');
    }

    // 1) Cargar destinatario
    $stmt = $conn->prepare("
        SELECT f.id, f.numero, f.serie, u.email, p.nombre
        FROM facturas f
        JOIN usuarios u ON u.uuid = f.usuario_uuid
        LEFT JOIN usuarios_perfil AS p ON u.uuid = p.usuario_uuid
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

    $email = trim((string) ($row['email'] ?? ''));
    $nombre = trim((string) ($row['nombre'] ?? ''));
    $serie = (string) ($row['serie'] ?? '');
    $numero = (string) ($row['numero'] ?? '');

    if ($email === '') {
        return vp2_err(
            'La factura no tiene email de destinatario.',
            'FACTURA_EMAIL_EMPTY',
            [
                'data' => ['idFactura' => $idFactura],
            ],
        );
    }

    // 2) Evitar reenvío en CRON
    if (!empty($opts['skip_if_already_sent']) && empty($opts['force_send'])) {
        $chk = $conn->prepare("
            SELECT id FROM facturas_logs
            WHERE factura_id = :fid AND accion = 'envio_email'
            LIMIT 1
        ");
        $chk->execute([':fid' => $idFactura]);
        if ($chk->fetch(PDO::FETCH_ASSOC)) {
            return vp2_ok('Email ya fue enviado anteriormente (skip cron).', [
                'idFactura' => $idFactura,
                'email' => $email,
                'skipped' => true,
            ]);
        }
    }

    // 3) Generar o reutilizar PDF
    $pdfRes = generarFacturaPdf($idFactura, [
        'mode' => 'F',
        'base_dir' => $opts['base_dir'],
        'subdir' => $opts['subdir'],
        'force' => (bool) $opts['force_pdf'],
    ]);

    if (($pdfRes['status'] ?? '') !== 'success') {
        return vp2_err(
            'No se pudo generar el PDF para enviar.',
            'FACTURA_PDF_ERROR',
            [
                'data' => ['idFactura' => $idFactura, 'pdfRes' => $pdfRes],
            ],
        );
    }

    $pdfPath = (string) ($pdfRes['path'] ?? '');
    if ($pdfPath === '' || !file_exists($pdfPath)) {
        return vp2_err('PDF no encontrado en disco.', 'FACTURA_PDF_MISSING', [
            'data' => ['path' => $pdfPath],
        ]);
    }

    // 4) Enviar email
    try {
        $safeName = sprintf('factura_%s_%s.pdf', $serie, $numero);

        $mailer = new Mailer();
        $sent = $mailer->send(
            to: $email,
            toName: $nombre !== '' ? $nombre : $email,
            subject: (string) $opts['subject'],
            htmlBody: (string) ($opts['body_html'] ?? $opts['body_text']),
            plainText: (string) $opts['body_text'],
            bcc: (array) $opts['bcc'],
            replyTo: $opts['reply_to'],
            attachments: [['path' => $pdfPath, 'name' => $safeName]],
        );

        if (!$sent) {
            throw new \RuntimeException('Mailer::send() devolvió false.');
        }

        $usuarioBackofficeId =
            $opts['origen'] === 'intranet'
                ? getUsuarioBackofficeIdFromCookie()
                : null;

        registrarLogFactura(
            $conn,
            $idFactura,
            $usuarioBackofficeId,
            'envio_email',
            [
                'email' => $email,
                'pdf' => $pdfPath,
                'origen' => $opts['origen'],
                'force_send' => (bool) $opts['force_send'],
            ],
        );

        return vp2_ok('Factura enviada correctamente por email.', [
            'idFactura' => $idFactura,
            'email' => $email,
            'pdf' => $pdfPath,
        ]);
    } catch (\Throwable $e) {
        $usuarioBackofficeId =
            $opts['origen'] === 'intranet'
                ? getUsuarioBackofficeIdFromCookie()
                : null;

        registrarLogFactura(
            $conn,
            $idFactura,
            $usuarioBackofficeId,
            'envio_email_error',
            [
                'email' => $email,
                'origen' => $opts['origen'],
                'error' => $e->getMessage(),
            ],
        );

        error_log('[FINGUER] Error enviando factura: ' . $e->getMessage());

        return vp2_err(
            'Error enviando la factura por email.',
            'FACTURA_EMAIL_ERROR',
            [
                'data' => ['error' => $e->getMessage()],
            ],
        );
    }
}
