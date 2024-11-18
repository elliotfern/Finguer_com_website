<?php
global $conn;

$id = $params['id'];
$email_pass = $_ENV['EMAIL_PASS'];

// Incluye la clase PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Incluye los archivos autoload de PHPMailer
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');


if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.idClient, r.importe, r.processed, r.fechaReserva, r.tipo, u.email, u.nombre, r.horaEntrada, r.diaEntrada, r.horaSalida, r.diaSalida, r.vehiculo, r.matricula, r.vuelo, r.limpieza, r.notes, r.buscadores
        FROM reserves_parking AS r
        LEFT JOIN usuaris AS u ON r.idClient = u.id
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva_old = $row['idReserva'];
            $idClient_old = $row['idClient'];
            $importe_old = $row['importe'];
            $processed_old = $row['processed'];
            $fechaReserva_old = $row['fechaReserva'];
            $nombre_old = $row['nombre'];
            $email_old = $row['email'];
            $horaEntrada_old = $row['horaEntrada'];
            $diaEntrada_old = $row['diaEntrada'];
            $fecha_formateada1 = date('d-m-Y', strtotime($diaEntrada_old));
            $horaSalida_old = $row['horaSalida'];
            $diaSalida_old = $row['diaSalida'];
            $fecha_formateada2 = date('d-m-Y', strtotime($diaSalida_old));
            $vehiculo_old = $row['vehiculo'];
            $matricula_old = $row['matricula'];
            $vuelo_old = $row['vuelo'];

            $tipo = $row['tipo'];
            if ($tipo == 1) {
                $tipoReserva2 = "Finguer Class";
            } elseif ($tipo == 2) {
                 $tipoReserva2 = "Gold Finguer Class";
            } else {
                $tipoReserva2 = "Finguer Class";
            }
            $limpieza = $row['limpieza'];
            if ($limpieza == 1) {
                $limpieza2 = "Servicio de limpieza exterior";
            } elseif ($limpieza == 2) {
                 $limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
            } elseif ($limpieza == 3) {
                $limpieza2 = "Limpieza PRO";
            } else {
                $limpieza2 = "Sin servicio de limpieza.";
            }

            $notes_old = $row['notes'];
            $buscadores_old = $row['buscadores'];
        }

        echo "<div class='container'>
        <h2>Enviament correu electrònic de confirmació de reserva (ID Reserva: ".$idReserva_old.") </h2>";
        
            // aqui comença l'enviament
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
                $mail->addAddress($email_old, $nombre_old);

                // Añade destinatarios ocultos (BCC) si es necesario
                $mail->addBCC('hello@finguer.com');
                $mail->addBCC('elliotfernandez87@gmail.com');

                // Configura el asunto y el cuerpo del correo electrónico
                $mail->isHTML(true);
                $mail->Subject = 'Confirmación de su reserva en Finguer.com';
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
                                <h1 style="color: #ffffff; margin: 0;">Confirmación de Reserva de Parking en Finguer.com</h1>
                            </td>
                        </tr>
                        <tr>
                            <td style="padding: 40px 30px;">
                                <p>Estimado/a '.$nombre_old.',</p>
                                <p>Su reserva de parking ha sido confirmada con éxito. A continuación, encontrará los detalles de su reserva:</p>
                                <ul>
                                    <li><strong>Tipo de servicio:</strong> '.$tipoReserva2.'</li>
                                    <li><strong>Limpieza:</strong> '.$limpieza2.'</li>
                                    <li><strong>Fecha de entrada: '.$fecha_formateada1.' - '.$horaEntrada_old.'</strong></li>
                                    <li><strong>Fecha de salida: '.$fecha_formateada2.' - '.$horaSalida_old.'</strong></li>
                                    <li><strong>Precio (IVA incluido) '.$importe_old.' €</strong></li>
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
                echo 'El correu electrònic s\'ha enviat correctament';
            } catch (Exception $e) {
                echo "El correu electrònic no s\'ha pogut enviar. Error: {$mail->ErrorInfo}";
            }
        echo "</div>";

    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

echo "</div>";

require_once(APP_ROOT . '/public/inc/footer.php');
?>