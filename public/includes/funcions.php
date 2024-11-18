<?php
use RedsysConsultasPHP\Client\Client;

// Incluye la clase PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Dotenv\Dotenv;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

function sanitize_html($html) {
    $config = HTMLPurifier_Config::createDefault();
    $config->set('HTML.Allowed', 'p,b,a[href],i,em,strong,ul,ol,li,br'); // Define las etiquetas y atributos permitidos
    $purifier = new HTMLPurifier($config);
    return $purifier->purify($html);
}

function data_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    return $data;
  }

// Función que verifica si el usuario tiene un token válido
function verificarSesion() {
    // Inicia la sesión si no está ya iniciada
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Verifica si la cookie del token existe y es válida
    if (!isset($_COOKIE['token']) || !validarToken($_COOKIE['token'])) {
        header('Location: /control/login'); // Redirige a login si no hay token válido
        exit();
    }
}

function validarToken($jwt) {
   
    $jwtSecret = $_ENV['TOKEN'];  // Tu clave secreta
    $decoded = null;

    try {

        $decoded = JWT::decode($jwt, new key ($jwtSecret, 'HS256'));

       // Verifica si el token ha expirado
        if (isset($decoded->exp) && $decoded->exp < time()) {
            return false;  // Token expirado
        }

    } catch (Exception $e) {
        // Manejo del error
        error_log('Error al validar el token: ' . $e->getMessage());  // Log del error para depuración
        return false;
    }

    // Si la decodificación es exitosa y el token es válido, se devuelve el payload
    return $decoded;
}

function verificarPagament($id) {
    
    global $conn;
    
    $id = $id;
    
    if (is_numeric($id)) {
        $id_old = intval($id);
        
        if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
            $codi_resposta = 2;
    
            // consulta general reserves 
            $sql = "SELECT r.idReserva
            FROM reserves_parking AS r
            WHERE r.id = $id_old";
    
            $pdo_statement = $conn->prepare($sql);
            $pdo_statement->execute();
            $result = $pdo_statement->fetchAll();
            
            foreach($result as $row) {
                $idReserva = $row['idReserva'];
            }
        
            $token = $_ENV['MERCHANTCODE'];
            $token2 = $_ENV['KEY'];
            $token3 = $_ENV['TERMINAL'];
            $url_Ok = $_ENV['URLOK'];
            $url_Ko = $_ENV['URLKO'];
            $url = 'https://finguer.com/compra-realizada';
    
            $url = 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
            $client = new Client($url, $token2);
    
            $order = $idReserva;
            $terminal = '1';
            $merchant_code = $token;
            $response = $client->getTransaction($order, $terminal, $merchant_code);
    
            // Supongamos que $response contiene el objeto Transaction
            // Acceder a las propiedades protegidas mediante reflexión
    
            // Función para obtener el valor de una propiedad protegida de un objeto
            if (!function_exists('getProtectedPropertyValue')) {
                function getProtectedPropertyValue($object, $propertyName) {
                    $reflection = new ReflectionClass($object);
                    $property = $reflection->getProperty($propertyName);
                    $property->setAccessible(true);
                    return $property->getValue($object);
                }
            }

            try {
                $response = $client->getTransaction($order, $terminal, $merchant_code);
    
                if (!$response) {
                    throw new Exception("No s'ha obtingut cap resposta de la API de RedSys.");
                }
    
                // Acceder a las propiedades
                $ds_response = getProtectedPropertyValue($response, 'Ds_Response');
    
                // Verificar el valor de Ds_Response
                switch ($ds_response) { 
                    case '9218':
                        echo "<div class='alert alert-danger text-center' role='alert'>
                                <p><strong>Pagament fallit</strong>.</p>
                              </div>";
                        break;
    
                    case '0000':
                        echo "<div class='alert alert-success text-center' role='alert'>
                                <p><strong>Pagament verificat correctament amb RedSys.</strong></p>
                              </div>";
    
                        // Ara camviem l'estat del pagament a la base de dades
    
                        $processed = 1;
            
                        $sql = "UPDATE reserves_parking SET processed=:processed
                        WHERE id=:id";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(":processed", $processed, PDO::PARAM_INT);
                        $stmt->bindParam(":id", $id_old, PDO::PARAM_INT);
                        $stmt->execute();

                        enviarConfirmacio($id_old);
                        enviarFactura($id_old);
                        break;
    
                    default:
                        echo "<div class='alert alert-danger text-center' role='alert'>
                                <p><strong>No s'ha pogut verificar aquest pagament. Pagament fallit o denegat amb RedSys.</strong></p>
                              </div>";
                        break;
                }
            } catch (Exception $e) {
                // Manejar el error de la API de Redsys
                echo "<div class='alert alert-danger text-center' role='alert'>
                        <p><img src='".APP_WEB."/inc/img/warning.png' alt='Pagament Error'></p>
                        <p><strong>Error de pagament: " . htmlspecialchars($e->getMessage()) . "</strong></p>";
                            if ($e->getMessage() === 'Error XML0024') {
                                // Mostrar mensaje para el error específico
                                echo "<p><strong>Missatge de Redsys: No existen operaciones para los datos solicitados.</strong></p>";
                            }
                        echo "</div>";    
            }  
    
        } else {
            echo "Error: aquest ID no és vàlid";
        }
    } else {
        echo "Error. No has seleccionat cap reserva.";
    }
}

function enviarConfirmacio($id) {
    global $conn;

    $id = $id;
    $email_pass = $_ENV['EMAIL_PASS'];
    $brevoApi = $_ENV['BREVO_API'];

    // Incluye los archivos autoload de PHPMailer
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');


    if (is_numeric($id)) {
        $id_old = intval($id);
        
        if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
            $codi_resposta = 2;

            // consulta general reserves 
            $sql = "SELECT r.idReserva, r.idClient, r.processed, r.fechaReserva, r.tipo, u.email, u.nombre, r.horaEntrada, r.diaEntrada, r.horaSalida, r.diaSalida, r.vehiculo, r.matricula, r.vuelo, r.limpieza, r.notes, r.buscadores, r.importe, r.subTotal, r.importeIva, r.costeReserva, r.costeSeguro, r.costeLimpieza
            FROM reserves_parking AS r
            LEFT JOIN usuaris AS u ON r.idClient = u.id
            WHERE r.id = $id_old";

            $pdo_statement = $conn->prepare($sql);
            $pdo_statement->execute();
            $result = $pdo_statement->fetchAll();
            foreach($result as $row) {
                $idReserva_old = $row['idReserva'];
                $idClient_old = $row['idClient'];
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
                $importe_old = $row['importe'];
                $subTotal_old = $row['subTotal'];
                $importeIva_old = $row['importeIva'];
                $costeReserva_old = $row['costeReserva'];
                $costeSeguro_old = $row['costeSeguro'];
                $costeLimpieza_old = $row['costeLimpieza'];

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
                    $mail->Host       = 'smtp-relay.brevo.com'; // Servidor SMTP de Brevo
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '7a0605001@smtp-brevo.com'; // Tu dirección de correo de Brevo
                    $mail->Password   = $brevoApi; // Tu contraseña de Brevo o API key
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar encriptación TLS
                    $mail->Port       = 587; // Puerto SMTP para TLS

                    // Configura el remitente y el destinatario
                    $mail->setFrom('hello@finguer.com', 'Finguer.com');
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
}

function enviarFactura($id) {
    global $conn;
    $brevoApi = $_ENV['BREVO_API'];

$id = $id;

require_once(APP_ROOT . '/vendor/tecnickcom/tcpdf/tcpdf.php');

// Incluye los archivos autoload de PHPMailer
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

$email_pass = $_ENV['EMAIL_PASS'];

// Incluye la clase TCPDF
//use \TCPDF;

if (is_numeric($id)) {
    $id_old = intval($id);
    
    if ( filter_var($id_old, FILTER_VALIDATE_INT) ) {
        $codi_resposta = 2;

        // consulta general reserves 
        $sql = "SELECT r.idReserva, r.idClient, r.processed, r.fechaReserva, r.tipo, r.horaEntrada, r.diaEntrada, r.horaSalida, r.diaSalida, r.vehiculo, r.matricula, r.vuelo, r.limpieza, r.notes, r.buscadores,
        u.email, 
        u.nombre,
        u.email,
        u.empresa,
        u.nif,
        u.direccion,
        u.ciudad,
        u.codigo_postal,
        u.pais,
        u.telefono, r.importe, r.subTotal, r.importeIva, r.costeReserva, r.costeSeguro, r.costeLimpieza, r.seguroCancelacion
        FROM reserves_parking AS r
        LEFT JOIN usuaris AS u ON r.idClient = u.id
        WHERE r.id = $id_old";

        $pdo_statement = $conn->prepare($sql);
        $pdo_statement->execute();
        $result = $pdo_statement->fetchAll();
        foreach($result as $row) {
            $idReserva_old = $row['idReserva'];
            $idClient_old = $row['idClient'];
            $processed_old = $row['processed'];
            $fechaReserva_old = $row['fechaReserva'];
            $fechaReserva = date('d-m-Y H:i:s', strtotime($fechaReserva_old));
            $fechaAnoReserva = date('Y', strtotime($fechaReserva_old));
            $horaEntrada_old = $row['horaEntrada'];
            $diaEntrada_old = $row['diaEntrada'];
            $fecha_formateada1 = date('d-m-Y', strtotime($diaEntrada_old));
            $horaSalida_old = $row['horaSalida'];
            $diaSalida_old = $row['diaSalida'];
            $fecha_formateada2 = date('d-m-Y', strtotime($diaSalida_old));
            $vehiculo_old = $row['vehiculo'];
            $matricula_old = $row['matricula'];
            $vuelo_old = $row['vuelo'];
            $importe_old = $row['importe'];
            $subTotal_old = $row['subTotal'];
            $importeIva_old = $row['importeIva'];
            $costeReserva_old = $row['costeReserva'];
            $costeSeguro_old = $row['costeSeguro'];
            $costeLimpieza_old = $row['costeLimpieza'];
            $seguroCancelacion_old = $row['seguroCancelacion'];

            
            if (is_numeric($costeSeguro_old) && $costeSeguro_old > 0) {
                $costeSeguro = number_format($costeSeguro_old, 2, ',', '') . " €";
            } else {
                $costeSeguro = "-";
            }

            if (is_numeric($costeLimpieza_old)) {
                $costeLimpieza = number_format($costeLimpieza_old, 2, ',', '') . " €";
            } else {
                $costeLimpieza = "-";
            }


            $nombre_old = $row['nombre'];
            $email_old = $row['email'];
            $empresa_old = $row['empresa'];
            $nif_old = $row['nif'];
            $direccion_old = $row['direccion'];
            $ciudad_old = $row['ciudad'];
            $codigo_postal_old = $row['codigo_postal'];
            $pais_old = $row['pais'];
            $telefono_old = $row['telefono'];

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
                $limpieza2 = "No Contratado";
            }

            if ($seguroCancelacion_old == 1 ) {
                $seguro = "Contratado";
            } else {
                $seguro = "No Contratado";
            }

            $notes_old = $row['notes'];
            $buscadores_old = $row['buscadores'];
        }

        echo "<div class='container'>
        <h2>Enviament de la factura PDF per correu electrònic (ID Reserva: ".$idReserva_old.") </h2>";
        
            // aqui comença l'enviament de la factura PDF
            $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
            $pdf->AddPage();

            // set header and footer fonts
            $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
            $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

           // set margins
           $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);

            // set auto page breaks
            $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

            // Agregar elementos HTML al PDF
            $htmlContent = '
            <div class="container">
            <div class="container">
            <img alt="Finguer" src="https://finguer.com/public/img/logo-header.svg" width="150" height="70">
            </div>
            <br>
            <strong>Número de factura: '.$id_old.'/'.$fechaAnoReserva.'</strong><br>
            Fecha de la factura: '.$fechaReserva.'<br>
            </div>
            
            <div class="container">
              <table class="table">
                      <thead>
                      <tr>
                        <th>
                            <strong>Facturado a:</strong><br>
                            '.$nombre_old.'<br>
                            '.$email_old.'<br>';

                            if (isset($empresa_old)) {
                                $htmlContent .= $empresa_old.'<br>';
                            }

                            if (isset($nif_old)) {
                                $htmlContent .= 'NIF/NIE/CIF: '.$nif_old.'<br>';
                            }

                            if (isset($direccion_old)) {
                                $htmlContent .= $direccion_old.'<br>
                                '.$ciudad_old.', '.$codigo_postal_old.'<br>
                                '.$pais_old.'<br>
                                Teléfono: '.$telefono_old.' ';
                            }
                            
                        $htmlContent .= '</th>
                        <th>
                        <strong>BCN PARKING S.L</strong><br>
                        CIF: B65548919<br>
                        Carrer de l\'Alt Camp, 9<br>
                        Sant Boi de Llobregat (Barcelona)<br>
                        Código postal: 08830<br>
                        ESPAÑA
                        </th>
                      </tr>
                      </thead>
              </table>
            </div>
            <div class="container">
            <h2 style="text-align: center;"><strong>DETALLES DE LA FACTURA</strong></h2>
                <div class="table-responsive">
                    <table cellpadding="5" cellspacing="0" style="border: 1px solid black;">
                        <thead>
                            <tr style="background-color: black; color: white;">
                                <th style="padding: 5px; border: 1px solid black;">Descripción producto</th>
                                <th style="padding: 5px; border: 1px solid black;">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">
                                Tipo de servicio: '.$tipoReserva2.'<br>
                                Fecha de entrada: '.$fecha_formateada1.' - '.$horaEntrada_old.'<br>
                                Fecha de salida: '.$fecha_formateada2.' - '.$horaSalida_old.'<br>
                                Vehículo: '.$vehiculo_old.'<br>
                                Matrícula: '.$matricula_old.'
                            </td>

                            <td style="padding: 5px; border: 1px solid black;">'.number_format($costeReserva_old, 2, ',', '').' €</td>
                        </tr>

                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">
                               <strong>Servicio de Limpieza:</strong><br>
                                '.$limpieza2.'
                            </td>

                            <td style="padding: 5px; border: 1px solid black;">'.$costeLimpieza.'</td>
                        </tr>   
                        
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">
                               <strong>Seguro de Cancelación de la Reserva:</strong><br>
                                '.$seguro.'
                                </td>

                            <td style="padding: 5px; border: 1px solid black;">'.$costeSeguro.'</td>
                        </tr>
                           
                           </tbody>                       
                    </table>
                </div>
            </div>
            
            <div class="container">
            <table cellpadding="5" cellspacing="0" style="border: 1px solid black; width: 50%;">
                <thead>
                    <tr>
                        <th scope="col" style="width: 50%;"></th>
                        <th scope="col" style="width: 50%;"></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="width: 50%;">Subtotal</td>
                        <td style="text-align: right; width: 50%;">'.number_format($subTotal_old, 2, ',', '').' €</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">IVA 21%</td>
                        <td style="text-align: right;">'.number_format($importeIva_old, 2, ',', '').' €</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Total</td>
                        <td style="text-align: right;"><strong>'.number_format($importe_old, 2, ',', '').' €</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        Muchas gracias por confiar en nuestros servicios. Esperamos que sea de su agrado.';

            // Escribir el contenido HTML en el PDF
            $pdf->writeHTML($htmlContent, true, false, true, false, '');

            $filename = APP_ROOT . '/pdf/documento.pdf'; // Nombre del archivo PDF generado
            $pdf->Output($filename, 'F'); // Guardar el PDF en el servidor

            // Configurar PHPMailer
            $mail = new PHPMailer(true); // Pasa true para habilitar excepciones
            $mail->CharSet = 'UTF-8';
            $mail->isSMTP();
                    $mail->Host       = 'smtp-relay.brevo.com'; // Servidor SMTP de Brevo
                    $mail->SMTPAuth   = true;
                    $mail->Username   = '7a0605001@smtp-brevo.com'; // Tu dirección de correo de Brevo
                    $mail->Password   = $brevoApi; // Tu contraseña de Brevo o API key
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; // Habilitar encriptación TLS
                    $mail->Port       = 587; // Puerto SMTP para TLS

        // Configurar remitente y destinatario
        $mail->setFrom('web@finguer.com', 'Finguer');
        $mail->addAddress($email_old, $nombre_old);

        $mail->addBCC('hello@finguer.com');
        $mail->addBCC('elliotfernandez87@gmail.com');

        // Adjuntar el archivo PDF generado
        $mail->addAttachment($filename);

        // Configurar el correo electrónico
        $mail->Subject = 'Factura servicios Finguer.com';
        $mail->Body = 'Adjunto encontrarás el documento PDF con tu factura.';

        // Enviar el correo electrónico
        if ($mail->send()) {
            echo 'El correo electrónico se envió correctamente.';
        } else {
            echo 'Hubo un error al enviar el correo electrónico: ' . $mail->ErrorInfo;
        }

        echo "</div>";

    } else {
        echo "Error: aquest ID no és vàlid";
    }
} else {
    echo "Error. No has seleccionat cap vehicle.";
}

}