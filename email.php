<?php

require 'vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

$url_root = $_SERVER['DOCUMENT_ROOT'];
define("APP_ROOT", $url_root);

$email_pass = $_ENV['EMAIL_PASS'];

// Incluye la clase PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye los archivos autoload de PHPMailer
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

// Crea una nueva instancia de PHPMailer
$mail = new PHPMailer(true); // Pasa true para habilitar excepciones

try {
    // Configura el servidor SMTP
    $mail->isSMTP();
    $mail->Host       = 'hl121.lucushost.org';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'web@finguer.com';
    $mail->Password   = $email_pass;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port       = 465;

    // Configura el remitente y el destinatario
    $mail->setFrom('web@finguer.com', 'Finguer.com');
    $mail->addAddress('elliotfernandez87@gmail.com', 'Nombre del Destinatario');

    // Añade destinatarios ocultos (BCC) si es necesario
    $mail->addBCC('elliot@hispantic.com');

    // Configura el asunto y el cuerpo del correo electrónico
    $mail->isHTML(true);
    $mail->Subject = 'Asunto del correo electrónico';
    $mail->CharSet = 'UTF-8';
    $mail->Body = '
        <!DOCTYPE html>
        <html lang="es">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Confirmación de Reserva efectuadamente correctamente en Finguer.com</title>
        </head>
        <body>
        <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0;">

        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff;">
            <tr>
                <td align="center" bgcolor="#007bff" style="padding: 40px 0;">
                    <h1 style="color: #ffffff; margin: 0;">Confirmación de Reserva de Parking</h1>
                </td>
            </tr>
            <tr>
                <td style="padding: 40px 30px;">
                    <p>Estimado/a [Nombre del Cliente],</p>
                    <p>Su reserva de parking ha sido confirmada con éxito. A continuación, encontrará los detalles de su reserva:</p>
                    <ul>
                        <li><strong>Fecha de entrada:</strong> [Fecha de Reserva]</li>
                        <li><strong>Días:</strong> [Duración]</li>
                        <li><strong>Lugar de Parking:</strong> Carrer de l\'Alt Camp, 9, 08830 Sant Boi de Llobregat, (Barcelona) España</li>
                    </ul>
                    <p>Por favor, asegúrese de llegar a tiempo y tener su reserva a mano para su presentación.</p>
                    <p>Si tiene alguna pregunta o necesita más información, no dude en ponerse en contacto con nosotros.</p>
                    <p>Gracias por elegir nuestro servicio de parking.</p>
                    <p>Atentamente,</p>
                    <p>BCN Parking SL - Finguer-com</p>
                </td>
            </tr>
            <tr>
                <td align="center" bgcolor="#007bff" style="padding: 20px 30px;">
                    <p style="color: #ffffff; margin: 0;">Este correo electrónico fue enviado automáticamente. Por favor no respondas a este mensaje.</p>
                </td>
            </tr>
        </table>
        </body>
        </html>
    ';

    // Envía el correo electrónico
    $mail->send();
    echo 'El correo electrónico ha sido enviado correctamente.';
} catch (Exception $e) {
    echo "El correo electrónico no pudo ser enviado. Error: {$mail->ErrorInfo}";
}

?>