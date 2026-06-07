<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarNotificacionContacto(PDO $conn, int $contactoId): array
{

    global $conn;
    /** @var PDO $conn */

    // 1) cargar contacto
    $stmt = $conn->prepare("
        SELECT *
        FROM formulario_contacto
        WHERE id = :id
        LIMIT 1
    ");
    $stmt->execute([':id' => $contactoId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [
            'status' => 'error',
            'message' => 'Contacto no encontrado'
        ];
    }

    $nombre   = (string)$row['nombre'];
    $email    = (string)$row['email'];
    $telefono = (string)$row['telefono'];
    $mensaje  = (string)$row['mensaje'];
    $fecha    = (string)$row['created_at'];

    // 2) BREVO API
    $brevoApi = (string)($_ENV['BREVO_API'] ?? '');

    if ($brevoApi === '') {
        return [
            'status' => 'error',
            'message' => 'BREVO_API no configurada'
        ];
    }

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
        $mail->addAddress('elliot@hispantic.com', 'Elliot Admin');

        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'Nuevo contacto desde formulario Finguer';

        $mail->Body = '
        <!DOCTYPE html>
        <html lang="es">
        <body style="font-family:Arial; background:#f4f4f4; padding:20px;">

        <table width="600" align="center" style="background:#fff; padding:20px;">
            <tr>
                <td style="background:#007bff; color:#fff; padding:15px; text-align:center;">
                    <h2 style="margin:0;">Has recibido un nuevo formulario de contacto en Finguer.com</h2>
                </td>
            </tr>

            <tr>
                <td style="padding:20px;">
                    <p><strong>Nombre:</strong> ' . htmlspecialchars($nombre) . '</p>
                    <p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>
                    <p><strong>Teléfono:</strong> ' . htmlspecialchars($telefono) . '</p>
                    <p><strong>Fecha:</strong> ' . htmlspecialchars($fecha) . '</p>

                    <hr>

                    <p><strong>Mensaje:</strong></p>
                    <p>' . nl2br(htmlspecialchars($mensaje)) . '</p>
                </td>
            </tr>

            <tr>
                <td style="background:#007bff; color:#fff; text-align:center; font-size:12px; padding:10px;">
                    Finguer.com - Sistema de contactos
                </td>
            </tr>
        </table>

        </body>
        </html>';

        $mail->send();

        return [
            'status' => 'success',
            'message' => 'Email enviado correctamente'
        ];
    } catch (\Throwable $e) {

        return [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
