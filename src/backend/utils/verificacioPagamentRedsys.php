<?php

use RedsysConsultasPHP\Client\Client;

// Incluye la clase PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// verifactu
use eseperio\verifactu\Verifactu;
use eseperio\verifactu\models\InvoiceSubmission;
use eseperio\verifactu\models\InvoiceId;
use eseperio\verifactu\models\Breakdown;
use eseperio\verifactu\models\BreakdownDetail;
use eseperio\verifactu\models\Chaining;
use eseperio\verifactu\models\ComputerSystem;
use eseperio\verifactu\models\LegalPerson;
use eseperio\verifactu\models\enums\InvoiceType;
use eseperio\verifactu\models\enums\TaxType;
use eseperio\verifactu\models\enums\YesNoType;
use eseperio\verifactu\models\enums\HashType;
use eseperio\verifactu\models\enums\OperationQualificationType;
use eseperio\verifactu\models\InvoiceResponse;
use eseperio\verifactu\services\QrGeneratorService;

function verificarPagament($id, $ejecutarAcciones = false)
{
    if (!is_numeric($id)) {
        return [
            'status'  => 'error',
            'message' => 'No se ha seleccionado ninguna reserva.'
        ];
    }

    $idReservaNueva = (int)$id;

    if (!filter_var($idReservaNueva, FILTER_VALIDATE_INT)) {
        return [
            'status'  => 'error',
            'message' => 'ID no válido.'
        ];
    }

    global $conn;

    // 1) Buscar la reserva en la BD NUEVA
    $sql = "
        SELECT 
            pr.id,
            pr.localizador,
            pr.usuario_id
        FROM epgylzqu_parking_finguer_v2.parking_reservas pr
        WHERE pr.id = :id
        LIMIT 1
    ";

    /** @var PDO $conn */
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idReservaNueva, PDO::PARAM_INT);
    $stmt->execute();
    $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$reserva) {
        return [
            'status'  => 'error',
            'message' => 'No se encontró la reserva en la nueva base de datos.'
        ];
    }

    $localizador = $reserva['localizador']; // esto es el Ds_Order que mandamos a Redsys




    // 2) Llamar a Redsys (igual que antes)
    $token        = $_ENV['MERCHANTCODE'];
    $token2       = $_ENV['KEY'];
    $url          = 'https://sis.redsys.es/apl02/services/SerClsWSConsulta';
    $client       = new Client($url, $token2);

    $order        = $localizador; // Ds_Order
    $terminal     = '1';
    $merchantCode = $token;

    if (!function_exists('getProtectedPropertyValue')) {
        function getProtectedPropertyValue($object, $propertyName)
        {
            // Si no es un objeto, no podemos leer propiedades
            if (!is_object($object)) {
                return null;
            }

            $reflection = new ReflectionClass($object);

            if (!$reflection->hasProperty($propertyName)) {
                return null;
            }

            $property = $reflection->getProperty($propertyName);
            $property->setAccessible(true);

            return $property->getValue($object);
        }
    }



    try {
        $response = $client->getTransaction($order, $terminal, $merchantCode);

        // NUEVO: si la respuesta es null o no es un objeto, corta aquí.
        if (!is_object($response)) {
            error_log(
                'Redsys getTransaction devolvió respuesta no válida. ' .
                    'order=' . $order .
                    ' terminal=' . $terminal .
                    ' merchant=' . $merchantCode .
                    ' response=' . var_export($response, true)
            );

            return [
                'status'  => 'error',
                'message' => 'Error Redsys: respuesta vacía o inválida.',
            ];
        }

        $ds_response = getProtectedPropertyValue($response, 'Ds_Response');

        if ($ds_response === null) {
            error_log(
                'No se pudo obtener Ds_Response de la respuesta Redsys: ' .
                    var_export($response, true)
            );

            return [
                'status'  => 'error',
                'message' => 'Error Redsys: no se pudo leer Ds_Response.',
            ];
        }


        switch ($ds_response) {
            case '9218':
                return [
                    'status'  => 'error',
                    'message' => 'Error 9218 Redsys: Pago no procesado.',
                ];

            case '0000':
                // Pago OK
                $data = [
                    'status'  => 'success',
                    'message' => 'Redsys: Pago verificado correctamente.',
                ];

                if ($ejecutarAcciones) {

                    // 3) Acciones en BD nueva: marcar pago + reserva
                    try {
                        $conn->beginTransaction();

                        // 3.1 Calcular importe total (base + IVA) desde los servicios
                        $sqlTotales = "
                            SELECT 
                                subtotal_calculado,
                                iva_calculado,
                                total_calculado
                            FROM epgylzqu_parking_finguer_v2.parking_reservas
                            WHERE id = :reserva_id
                            LIMIT 1
                        ";
                        $stmtTot = $conn->prepare($sqlTotales);
                        $stmtTot->bindParam(':reserva_id', $idReservaNueva, PDO::PARAM_INT);
                        $stmtTot->execute();
                        $totales = $stmtTot->fetch(PDO::FETCH_ASSOC);

                        $subtotal = (float)($totales['subtotal_calculado'] ?? 0);
                        $iva      = (float)($totales['iva_calculado'] ?? 0);
                        $total    = (float)($totales['total_calculado'] ?? ($subtotal + $iva));

                        // 3.2 Insertar en pagos (si no existe ya un pago confirmado)
                        $sqlPagoExiste = "
                            SELECT id 
                            FROM epgylzqu_parking_finguer_v2.pagos
                            WHERE reserva_id = :reserva_id
                              AND estado = 'confirmado'
                            LIMIT 1
                        ";
                        $stmtPagoEx = $conn->prepare($sqlPagoExiste);
                        $stmtPagoEx->bindParam(':reserva_id', $idReservaNueva, PDO::PARAM_INT);
                        $stmtPagoEx->execute();
                        $pagoExistente = $stmtPagoEx->fetch(PDO::FETCH_ASSOC);

                        $pagoId = null;

                        if (!$pagoExistente) {
                            $sqlInsertPago = "
                                INSERT INTO epgylzqu_parking_finguer_v2.pagos
                                (reserva_id, factura_id, fecha, metodo, importe, estado, pasarela, pedido_pasarela)
                                VALUES
                                (:reserva_id, NULL, NOW(), 'tarjeta', :importe, 'confirmado', 'REDSYS', :pedido)
                            ";
                            $stmtPago = $conn->prepare($sqlInsertPago);
                            $stmtPago->bindParam(':reserva_id', $idReservaNueva, PDO::PARAM_INT);
                            $stmtPago->bindParam(':importe', $total, PDO::PARAM_STR);
                            $stmtPago->bindParam(':pedido', $localizador, PDO::PARAM_STR);
                            $stmtPago->execute();

                            $pagoId = (int)$conn->lastInsertId();
                        } else {
                            $pagoId = (int)$pagoExistente['id'];
                        }

                        // 3.3 Marcar reserva como pagada
                        $sqlUpdReserva = "
                            UPDATE epgylzqu_parking_finguer_v2.parking_reservas
                            SET estado = 'pagada'
                            WHERE id = :id
                        ";
                        $stmtUpd = $conn->prepare($sqlUpdReserva);
                        $stmtUpd->bindParam(':id', $idReservaNueva, PDO::PARAM_INT);
                        $stmtUpd->execute();

                        // ⚠️ Aquí en el futuro:
                        // - crear factura + facturas_lineas
                        // - actualizar pagos.factura_id
                        // De momento solo marcamos pagada y registramos pago.

                        $conn->commit();
                    } catch (\Throwable $e) {
                        if ($conn->inTransaction()) {
                            $conn->rollBack();
                        }
                        return [
                            'status'  => 'error',
                            'message' => 'Error al actualizar el pago en la base de datos.',
                        ];
                    }

                    // TODO: cuando adaptemos enviarConfirmacio/enviarFactura a la BD nueva,
                    enviarConfirmacio($idReservaNueva);
                    $facturaId = crearFacturaParaReserva($conn, $idReservaNueva, 'redsys');
                    enviarFactura($facturaId);

                    return $data;
                } else {
                    // Solo informar, sin tocar BD
                    return $data;
                }

            default:
                return [
                    'status'  => 'error',
                    'message' => 'Error Redsys: No se ha podido verificar el pago.',
                ];
        }
    } catch (\Exception $e) {
        // Manejar el error de la API de Redsys
        if ($e->getMessage() === 'Error XML0024') {
            return [
                'status'  => 'error',
                'message' => 'Error XML0024 Redsys: No existen operaciones para los datos solicitados.'
            ];
        }

        return [
            'status'  => 'error',
            'message' => 'Error Redsys'
        ];
    }
}




function enviarConfirmacio($id)
{
    global $conn;

    $brevoApi = $_ENV['BREVO_API'];

    // Incluye PHPMailer
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

    if (!is_numeric($id)) {
        echo json_encode(["message" => "error"]);
        return;
    }

    $idReserva = (int)$id;

    if (!filter_var($idReserva, FILTER_VALIDATE_INT)) {
        echo json_encode(["message" => "error"]);
        return;
    }

    // 1) Datos de la reserva + usuario (NUEVO MODELO)
    $sql = "
        SELECT
            pr.id,
            pr.localizador,
            pr.fecha_reserva,
            pr.entrada_prevista,
            pr.salida_prevista,
            pr.tipo,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.subtotal_calculado,
            pr.iva_calculado,
            pr.total_calculado,
            pr.personas,

            u.email,
            u.nombre,
            u.telefono
        FROM epgylzqu_parking_finguer_v2.parking_reservas pr
        LEFT JOIN epgylzqu_parking_finguer_v2.usuarios u
            ON pr.usuario_id = u.id
        WHERE pr.id = :id
        LIMIT 1
    ";

    /** @var PDO $conn */
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(":id", $idReserva, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(["message" => "error"]);
        return;
    }

    $localizador       = $row['localizador'];
    $fechaReserva_old  = $row['fecha_reserva'];
    $fechaReserva_fmt  = date('d-m-Y H:i:s', strtotime($fechaReserva_old));

    $entradaPrevista   = $row['entrada_prevista'];
    $salidaPrevista    = $row['salida_prevista'];

    $diaEntrada_old    = date('Y-m-d', strtotime($entradaPrevista));
    $horaEntrada_old   = date('H:i',   strtotime($entradaPrevista));
    $diaSalida_old     = date('Y-m-d', strtotime($salidaPrevista));
    $horaSalida_old    = date('H:i',   strtotime($salidaPrevista));

    $fecha_formateada1 = date('d-m-Y', strtotime($diaEntrada_old));
    $fecha_formateada2 = date('d-m-Y', strtotime($diaSalida_old));

    $vehiculo_old      = $row['vehiculo'];
    $matricula_old     = $row['matricula'];
    $vuelo_old         = $row['vuelo'];

    $importe_old       = (float)$row['total_calculado'];
    $subTotal_old      = (float)$row['subtotal_calculado'];
    $importeIva_old    = (float)$row['iva_calculado'];

    $nombre_old        = $row['nombre'];
    $email_old         = $row['email'];

    $tipo              = (int)$row['tipo'];
    if ($tipo === 1) {
        $tipoReserva2 = "Finguer Class";
    } elseif ($tipo === 2) {
        $tipoReserva2 = "Gold Finguer Class";
    } else {
        $tipoReserva2 = "Finguer Class";
    }

    // 2) Servicio de limpieza desde parking_reservas_servicios
    $sqlLimpieza = "
        SELECT s.codigo, s.nombre
        FROM epgylzqu_parking_finguer_v2.parking_reservas_servicios prs
        JOIN epgylzqu_parking_finguer_v2.parking_servicios_catalogo s
          ON s.id = prs.servicio_id
        WHERE prs.reserva_id = :reserva_id
          AND s.codigo IN ('LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO')
        LIMIT 1
    ";
    $stmtLimp = $conn->prepare($sqlLimpieza);
    $stmtLimp->bindParam(":reserva_id", $idReserva, PDO::PARAM_INT);
    $stmtLimp->execute();
    $rowLimp = $stmtLimp->fetch(PDO::FETCH_ASSOC);

    if ($rowLimp) {
        switch ($rowLimp['codigo']) {
            case 'LIMPIEZA_EXT':
                $limpieza2 = "Servicio de limpieza exterior";
                break;
            case 'LIMPIEZA_EXT_INT':
                $limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
                break;
            case 'LIMPIEZA_PRO':
                $limpieza2 = "Limpieza PRO";
                break;
            default:
                $limpieza2 = $rowLimp['nombre'];
        }
    } else {
        $limpieza2 = "Sin servicio de limpieza.";
    }

    // 3) Email de confirmación (similar al antiguo)
    $mail = new PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = '7a0605001@smtp-brevo.com';
        $mail->Password   = $brevoApi;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('hello@finguer.com', 'Finguer.com');
        $mail->addAddress($email_old, $nombre_old);

        $mail->addBCC('hello@finguer.com');
        $mail->addBCC('elliotfernandez87@gmail.com');

        $mail->isHTML(true);
        $mail->Subject = 'Confirmación de su reserva en Finguer.com';
        $mail->CharSet = 'UTF-8';

        $mail->Body = '
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Confirmación de Reserva en Finguer.com</title>
            </head>
            <body style="font-family: Arial, sans-serif; background-color: #f4f4f4; color: #333; margin: 0; padding: 0;">

            <table align="center" border="0" cellpadding="0" cellspacing="0" width="600" style="border-collapse: collapse; background-color: #ffffff;">
                <tr>
                    <td align="center" bgcolor="#007bff" style="padding: 40px 0;">
                        <h1 style="color: #ffffff; margin: 0;">Confirmación de Reserva de Parking en Finguer.com</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding: 40px 30px;">
                        <p>Estimado/a ' . htmlspecialchars($nombre_old) . ',</p>
                        <p>Su reserva de parking ha sido confirmada con éxito. A continuación, encontrará los detalles de su reserva:</p>
                        <ul>
                            <li><strong>Localizador:</strong> ' . htmlspecialchars($localizador) . '</li>
                            <li><strong>Tipo de servicio:</strong> ' . $tipoReserva2 . '</li>
                            <li><strong>Limpieza:</strong> ' . $limpieza2 . '</li>
                            <li><strong>Fecha de entrada: ' . $fecha_formateada1 . ' - ' . $horaEntrada_old . '</strong></li>
                            <li><strong>Fecha de salida: ' . $fecha_formateada2 . ' - ' . $horaSalida_old . '</strong></li>
                            <li><strong>Precio (IVA incluido): ' . number_format($importe_old, 2, ',', '') . ' €</strong></li>
                            <li><strong>Lugar de Parking:</strong> Carrer de l\'Alt Camp, 9, 08830 Sant Boi de Llobregat, (Barcelona) España</li>
                        </ul>
                        <p>Por favor, asegúrese de llegar a tiempo y tener su reserva a mano para su presentación.</p>
                        <p>Si tiene alguna pregunta o necesita más información, no dude en ponerse en contacto con nosotros.</p>
                        <p>Gracias por elegir nuestro servicio de parking.</p>
                        <p>Atentamente,</p>
                        <p>BCN Parking SL - Finguer.com</p>
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

        $mail->send();
        echo json_encode(["message" => "success"]);
    } catch (Exception $e) {
        echo json_encode(["message" => "error"]);
    }
}



function enviarFactura($idFactura)
{
    global $conn;
    $brevoApi = $_ENV['BREVO_API'];

    if (!is_numeric($idFactura)) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'No se ha seleccionado ninguna factura.',
        ]);
        return;
    }

    $idFactura = (int)$idFactura;

    require_once(APP_ROOT . '/vendor/tecnickcom/tcpdf/tcpdf.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
    require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

    // 1) Datos de la factura + reserva + usuario (NUEVO MODELO)
    $sql = "
        SELECT
            f.id,
            f.numero,
            f.serie,
            f.fecha_emision,
            f.subtotal,
            f.total,
            f.impuesto_total,

            pr.id              AS reserva_id,
            pr.entrada_prevista,
            pr.salida_prevista,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.tipo,

            u.nombre,
            u.email,
            u.empresa,
            u.nif,
            u.direccion,
            u.ciudad,
            u.codigo_postal,
            u.pais,
            u.telefono
        FROM epgylzqu_parking_finguer_v2.facturas f
        JOIN epgylzqu_parking_finguer_v2.parking_reservas pr
          ON f.reserva_id = pr.id
        JOIN epgylzqu_parking_finguer_v2.usuarios u
          ON f.usuario_id = u.id
        WHERE f.id = :id
        LIMIT 1
    ";
    /** @var PDO $conn */
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $idFactura, PDO::PARAM_INT);
    $stmt->execute();
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode([
            'status'  => 'error',
            'message' => 'No se encontró la factura.',
        ]);
        return;
    }

    // --------
    // Reconstruimos las variables que usabas antes
    // --------

    // Número de factura
    $numeroFactura = $row['serie'] . '/' . $row['numero']; // ej: 2025/00005

    // Fecha de emisión (antes fechaReserva)
    $fechaReserva_old = $row['fecha_emision'];
    $fechaReserva     = date('d-m-Y H:i:s', strtotime($fechaReserva_old));

    // Entrada / salida: vienen como DATETIME en parking_reservas
    $entradaPrevista = $row['entrada_prevista'];
    $salidaPrevista  = $row['salida_prevista'];

    $diaEntrada_old    = date('Y-m-d', strtotime($entradaPrevista));
    $horaEntrada_old   = date('H:i',   strtotime($entradaPrevista));
    $diaSalida_old     = date('Y-m-d', strtotime($salidaPrevista));
    $horaSalida_old    = date('H:i',   strtotime($salidaPrevista));
    $fecha_formateada1 = date('d-m-Y', strtotime($diaEntrada_old));
    $fecha_formateada2 = date('d-m-Y', strtotime($diaSalida_old));

    // Vehículo / matrícula / vuelo
    $vehiculo_old  = $row['vehiculo'];
    $matricula_old = $row['matricula'];
    $vuelo_old     = $row['vuelo']; // si luego lo quieres poner, lo tienes aquí

    // Totales legales
    $importe_old    = (float)$row['total'];
    $subTotal_old   = (float)$row['subtotal'];
    $importeIva_old = (float)$row['impuesto_total'];

    // Datos del cliente
    $nombre_old        = $row['nombre'];
    $email_old         = $row['email'];
    $empresa_old       = $row['empresa'];
    $nif_old           = $row['nif'];
    $direccion_old     = $row['direccion'];
    $ciudad_old        = $row['ciudad'];
    $codigo_postal_old = $row['codigo_postal'];
    $pais_old          = $row['pais'];
    $telefono_old      = $row['telefono'];

    // Tipo de servicio (1 / 2)
    $tipo = (int)$row['tipo'];
    if ($tipo === 1) {
        $tipoReserva2 = "Finguer Class";
    } elseif ($tipo === 2) {
        $tipoReserva2 = "Gold Finguer Class";
    } else {
        $tipoReserva2 = "Finguer Class";
    }

    // 2) Coste Reserva, Limpieza, Seguro, Limpieza2, SeguroCancelación
    //    -> ahora vienen de parking_reservas_servicios

    $reservaId = (int)$row['reserva_id'];

    $sqlServ = "
        SELECT s.codigo, s.nombre, prs.total_base
        FROM epgylzqu_parking_finguer_v2.parking_reservas_servicios prs
        JOIN epgylzqu_parking_finguer_v2.parking_servicios_catalogo s
          ON s.id = prs.servicio_id
        WHERE prs.reserva_id = :reserva_id
    ";
    $stmtServ = $conn->prepare($sqlServ);
    $stmtServ->bindParam(':reserva_id', $reservaId, PDO::PARAM_INT);
    $stmtServ->execute();
    $servicios = $stmtServ->fetchAll(PDO::FETCH_ASSOC);

    $costeReserva_old  = 0.0;
    $costeLimpieza_old = 0.0;
    $costeSeguro_old   = 0.0;
    $limpieza2         = "Sin servicio de limpieza";
    $seguro            = "No Contratado";

    foreach ($servicios as $srv) {
        $codigo = $srv['codigo'];
        $base   = (float)$srv['total_base'];

        switch ($codigo) {
            case 'RESERVA_FINGUER':
            case 'RESERVA_FINGUER_GOLD':
                $costeReserva_old = $base;
                break;

            case 'LIMPIEZA_EXT':
                $costeLimpieza_old = $base;
                $limpieza2 = "Servicio de limpieza exterior";
                break;

            case 'LIMPIEZA_EXT_INT':
                $costeLimpieza_old = $base;
                $limpieza2 = "Servicio de lavado exterior + aspirado tapicería interior";
                break;

            case 'LIMPIEZA_PRO':
                $costeLimpieza_old = $base;
                $limpieza2 = "Limpieza PRO";
                break;

            case 'SEGURO_CANCELACION':
                $costeSeguro_old = $base;
                $seguro = "Contratado";
                break;
        }
    }

    // Formatear valores como antes
    if (is_numeric($costeSeguro_old) && $costeSeguro_old > 0) {
        $costeSeguro = number_format($costeSeguro_old, 2, ',', '') . " €";
    } else {
        $costeSeguro = "-";
    }

    if (is_numeric($costeLimpieza_old) && $costeLimpieza_old > 0) {
        $costeLimpieza = number_format($costeLimpieza_old, 2, ',', '') . " €";
    } else {
        $costeLimpieza = "-";
    }

    // (seguroCancelacion_old ya no existe como campo;
    //  lo inferimos: si hay línea SEGURO_CANCELACION, lo consideramos contratado)
    $seguroCancelacion_old = ($costeSeguro_old > 0) ? 1 : 0;

    // ---------------------------------------------------------------------
    // 3) Generación del PDF (tu HTML casi tal cual, con las nuevas variables)
    // ---------------------------------------------------------------------

    $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
    $pdf->AddPage();

    $pdf->setHeaderFont(array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
    $pdf->setFooterFont(array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
    $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
    $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

    $htmlContent = '
        <div class="container">
            <div class="container">
                <img alt="Finguer" src="https://finguer.com/public/img/logo-header.svg" width="150" height="70">
            </div>
            <br>
            <strong>Número de factura: ' . htmlspecialchars($numeroFactura) . '</strong><br>
            Fecha de la factura: ' . $fechaReserva . '<br>
        </div>
        
        <div class="container">
            <table class="table">
                <thead>
                    <tr>
                        <th>
                            <strong>Facturado a:</strong><br>
                            ' . htmlspecialchars($nombre_old) . '<br>
                            ' . htmlspecialchars($email_old)  . '<br>';

    if (!empty($empresa_old)) {
        $htmlContent .= htmlspecialchars($empresa_old) . '<br>';
    }

    if (!empty($nif_old)) {
        $htmlContent .= 'NIF/NIE/CIF: ' . htmlspecialchars($nif_old) . '<br>';
    }

    if (!empty($direccion_old)) {
        $htmlContent .= htmlspecialchars($direccion_old) . '<br>'
            . htmlspecialchars($ciudad_old) . ', ' . htmlspecialchars($codigo_postal_old) . '<br>'
            . htmlspecialchars($pais_old) . '<br>'
            . 'Teléfono: ' . htmlspecialchars($telefono_old);
    }

    $htmlContent .= '
                        </th>
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
                                Tipo de servicio: ' . $tipoReserva2 . '<br>
                                Fecha de entrada: ' . $fecha_formateada1 . ' - ' . $horaEntrada_old . '<br>
                                Fecha de salida: ' . $fecha_formateada2 . ' - ' . $horaSalida_old . '<br>
                                Vehículo: ' . htmlspecialchars($vehiculo_old) . '<br>
                                Matrícula: ' . htmlspecialchars($matricula_old) . '
                            </td>
                            <td style="padding: 5px; border: 1px solid black;">' . number_format($costeReserva_old, 2, ',', '') . ' €</td>
                        </tr>

                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">
                               <strong>Servicio de Limpieza:</strong><br>
                                ' . $limpieza2 . '
                            </td>
                            <td style="padding: 5px; border: 1px solid black;">' . $costeLimpieza . '</td>
                        </tr>   
                        
                        <tr>
                            <td style="padding: 5px; border: 1px solid black;">
                               <strong>Seguro de Cancelación de la Reserva:</strong><br>
                                ' . $seguro . '
                            </td>
                            <td style="padding: 5px; border: 1px solid black;">' . $costeSeguro . '</td>
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
                        <td style="text-align: right; width: 50%;">' . number_format($subTotal_old, 2, ',', '') . ' €</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">IVA 21%</td>
                        <td style="text-align: right;">' . number_format($importeIva_old, 2, ',', '') . ' €</td>
                    </tr>
                    <tr>
                        <td style="width: 50%;">Total</td>
                        <td style="text-align: right;"><strong>' . number_format($importe_old, 2, ',', '') . ' €</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>
        
        Muchas gracias por confiar en nuestros servicios. Esperamos que sea de su agrado.
    ';

    $pdf->writeHTML($htmlContent, true, false, true, false, '');

    $numeroFactura = $row['serie'] . '_' . $row['numero']; // 2025_00023
    $filename = APP_ROOT . '/pdf/facturas/' . $numeroFactura . '.pdf';
    $pdf->Output($filename, 'F');

    // 4) Enviar email
    $mail = new PHPMailer(true);
    $mail->CharSet = 'UTF-8';
    $mail->isSMTP();
    $mail->Host       = 'smtp-relay.brevo.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = '7a0605001@smtp-brevo.com';
    $mail->Password   = $brevoApi;
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    $mail->setFrom('web@finguer.com', 'Finguer');
    $mail->addAddress($email_old, $nombre_old);
    $mail->addBCC('hello@finguer.com');
    $mail->addBCC('elliotfernandez87@gmail.com');

    $mail->addAttachment($filename);
    $mail->Subject = 'Factura servicios Finguer.com';
    $mail->Body    = 'Adjunto encontrarás el documento PDF con tu factura.';

    if ($mail->send()) {

        $usuarioBackofficeId = getUsuarioBackofficeIdFromCookie();

        registrarLogFactura($conn, $idFactura, $usuarioBackofficeId, 'envio_email', [
            'email' => $email_old,
        ]);

        echo json_encode([
            'status'  => 'success',
            'message' => 'Factura enviada correctamente por email al cliente.',
        ]);
    } else {

        $usuarioBackofficeId = getUsuarioBackofficeIdFromCookie();

        registrarLogFactura($conn, $idFactura, $usuarioBackofficeId, 'envio_email_error', [
            'email' => $email_old,
            'error' => $mail->ErrorInfo,
        ]);

        echo json_encode([
            'status'  => 'error',
            'codigo'  => $mail->ErrorInfo,
            'message' => 'Hubo un error al enviar la factura al cliente',
        ]);
    }
}


function enviarFacturaAVerifactu(int $idFactura): array
{
    global $conn;

    // 1) Cargar datos de la factura + reserva + usuario
    $sql = "
        SELECT
            f.id,
            f.numero,
            f.serie,
            f.fecha_emision,
            f.subtotal,
            f.impuesto_total,
            f.total,
            f.verifactu_estado,
            f.verifactu_hash,

            pr.entrada_prevista,
            pr.salida_prevista,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.tipo,

            u.nombre,
            u.email,
            u.empresa,
            u.nif,
            u.direccion,
            u.ciudad,
            u.codigo_postal,
            u.pais,
            u.telefono
        FROM epgylzqu_parking_finguer_v2.facturas f
        JOIN epgylzqu_parking_finguer_v2.parking_reservas pr
          ON f.reserva_id = pr.id
        JOIN epgylzqu_parking_finguer_v2.usuarios u
          ON f.usuario_id = u.id
        WHERE f.id = :id
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindValue(':id', $idFactura, PDO::PARAM_INT);
    $stmt->execute();
    $factura = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$factura) {
        return [
            'status'  => 'error',
            'message' => 'Factura no encontrada.'
        ];
    }

    // Si ya está aceptada en Verifactu, no reenviamos
    if (!empty($factura['verifactu_estado']) && $factura['verifactu_estado'] === 'aceptada') {
        return [
            'status'  => 'ok',
            'message' => 'La factura ya estaba enviada y aceptada en Verifactu.'
        ];
    }

    // 2) Construir el modelo InvoiceSubmission
    $invoice = new InvoiceSubmission();

    // 2.1 ID de factura (InvoiceId)
    $issueDate = (new DateTime($factura['fecha_emision']))->format('Y-m-d');
    $year      = (new DateTime($factura['fecha_emision']))->format('Y');

    // Formato serie/número para AEAT, p.ej. "6723/2025"
    $seriesNumber = $factura['numero'] . '/' . $year;

    $invoiceId = new InvoiceId();
    $invoiceId->issuerNif    = 'B65548919';         // CIF de BCN PARKING S.L (ajusta si es otro)
    $invoiceId->seriesNumber = $seriesNumber;
    $invoiceId->issueDate    = $issueDate;
    $invoice->setInvoiceId($invoiceId);

    // 2.2 Datos básicos factura
    $invoice->issuerName           = 'BCN PARKING S.L';
    $invoice->invoiceType          = InvoiceType::STANDARD;
    $invoice->operationDescription = 'Servicios de aparcamiento y extras';
    $invoice->taxAmount            = (float)$factura['impuesto_total'];
    $invoice->totalAmount          = (float)$factura['total'];
    $invoice->simplifiedInvoice    = YesNoType::NO;

    // ¿Tenemos destinatario identificado?
    $tieneNifReceptor = !empty($factura['nif']);
    $invoice->invoiceWithoutRecipient = $tieneNifReceptor ? YesNoType::NO : YesNoType::YES;

    // 2.3 Desglose de impuestos (solo un tramo 21%)
    $breakdown = new Breakdown();
    $detail = new BreakdownDetail();
    $detail->taxType               = TaxType::IVA;
    $detail->taxRate               = 21.00;
    $detail->taxableBase           = (float)$factura['subtotal'];
    $detail->taxAmount             = (float)$factura['impuesto'];
    $detail->operationQualification = OperationQualificationType::SUBJECT_NO_EXEMPT_NO_REVERSE;
    $breakdown->addDetail($detail);
    $invoice->setBreakdown($breakdown);

    // 2.4 Encadenamiento (chaining)
    $chaining = new Chaining();

    // Buscar última factura con hash para encadenar
    $sqlPrev = "
        SELECT
            numero,
            fecha_emision,
            verifactu_hash
        FROM epgylzqu_parking_finguer_v2.facturas
        WHERE id <> :id
          AND verifactu_hash IS NOT NULL
        ORDER BY fecha_emision DESC, numero DESC
        LIMIT 1
    ";
    $stmtPrev = $conn->prepare($sqlPrev);
    $stmtPrev->bindValue(':id', $idFactura, PDO::PARAM_INT);
    $stmtPrev->execute();
    $prev = $stmtPrev->fetch(PDO::FETCH_ASSOC);

    if ($prev && !empty($prev['verifactu_hash'])) {
        $prevYear   = (new DateTime($prev['fecha_emision']))->format('Y');
        $prevSeries = $prev['numero'] . '/' . $prevYear;

        $chaining->firstRecord = YesNoType::NO;
        $chaining->setPreviousInvoice([
            'seriesNumber' => $prevSeries,
            'issuerNif'    => 'B65548919',
            'issueDate'    => (new DateTime($prev['fecha_emision']))->format('Y-m-d'),
            'hash'         => $prev['verifactu_hash'],
        ]);
    } else {
        // Primera factura de la cadena
        $chaining->firstRecord = YesNoType::YES;
    }

    $invoice->setChaining($chaining);

    // 2.5 Información del sistema (ComputerSystem)
    $system = new ComputerSystem();
    $system->systemName          = 'Finguer Parking';
    $system->version             = '1.0';
    $system->providerName        = 'BCN PARKING S.L';
    $system->systemId            = '01';
    $system->installationNumber  = '1';
    $system->onlyVerifactu       = YesNoType::YES;
    $system->multipleObligations = YesNoType::NO;

    $provider = new LegalPerson();
    $provider->name = 'BCN PARKING S.L';
    $provider->nif  = 'B65548919';
    $system->setProviderId($provider);

    $invoice->setSystemInfo($system);

    // 2.6 Fechas / hash / etc.
    $now = new DateTime('now', new DateTimeZone('Europe/Madrid'));
    $invoice->recordTimestamp = $now->format(DATE_ATOM);  // 2025-12-04T17:30:00+01:00
    $invoice->hashType        = HashType::SHA_256;        // La librería calculará la huella
    $invoice->operationDate   = $issueDate;
    $invoice->externalRef     = 'RES-' . $factura['numero'];

    // 2.7 Receptor (si tiene NIF)
    if ($tieneNifReceptor) {
        $recipient = new LegalPerson();
        $recipient->name = $factura['nombre'];   // nombre cliente
        $recipient->nif  = $factura['nif'];      // NIF cliente
        $invoice->addRecipient($recipient);
    }

    // 2.8 Validar antes de enviar
    $validation = $invoice->validate();
    if ($validation !== true) {
        return [
            'status'  => 'error',
            'message' => 'Errores de validación en el modelo de factura Verifactu.',
            'errors'  => $validation,
        ];
    }

    // 3) Enviar a AEAT (Alta)
    try {
        $response = Verifactu::registerInvoice($invoice);

        // 3.1 Generar QR (necesita CSV de la respuesta)
        if ($response->submissionStatus === InvoiceResponse::STATUS_OK) {
            $invoice->csv = $response->csv;

            $qrPngData = Verifactu::generateInvoiceQr(
                $invoice,
                QrGeneratorService::DESTINATION_STRING,
                300,
                QrGeneratorService::RENDERER_GD
            );
            $qrBase64 = base64_encode($qrPngData);
        } else {
            $qrBase64 = null;
        }

        // 4) Guardar resultado en BD
        $estado   = ($response->submissionStatus === InvoiceResponse::STATUS_OK) ? 'aceptada' : 'error';
        $csv      = $response->csv ?? null;
        $hashAEAT = $invoice->hash ?? null;  // la librería ha calculado la huella
        $respJson = json_encode($response, JSON_UNESCAPED_UNICODE);

        $sqlUpdate = "
            UPDATE epgylzqu_parking_finguer_v2.facturas
            SET
                verifactu_estado      = :estado,
                verifactu_id_registro = :csv,
                verifactu_hash        = :hash,
                verifactu_qr_texto    = :qr,
                verifactu_xml         = NULL,
                verifactu_respuesta   = :respuesta
            WHERE id = :id
        ";

        $stmtUp = $conn->prepare($sqlUpdate);
        $stmtUp->bindValue(':estado',    $estado);
        $stmtUp->bindValue(':csv',       $csv);
        $stmtUp->bindValue(':hash',      $hashAEAT);
        $stmtUp->bindValue(':qr',        $qrBase64);
        $stmtUp->bindValue(':respuesta', $respJson);
        $stmtUp->bindValue(':id',        $idFactura, PDO::PARAM_INT);
        $stmtUp->execute();

        // 5) Respuesta amigable
        if ($response->submissionStatus === InvoiceResponse::STATUS_OK) {
            return [
                'status'  => 'success',
                'message' => 'Factura registrada correctamente en Verifactu.',
                'csv'     => $response->csv,
                'hash'    => $hashAEAT,
            ];
        }

        // Si no es OK, listamos errores de líneas
        $errores = [];
        foreach ($response->lineResponses as $lineResponse) {
            $errores[] = [
                'codigo'  => $lineResponse->errorCode,
                'mensaje' => $lineResponse->errorMessage,
            ];
        }

        return [
            'status'  => 'error',
            'message' => 'AEAT ha devuelto errores al registrar la factura.',
            'errors'  => $errores,
        ];
    } catch (\Throwable $e) {
        return [
            'status'  => 'error',
            'message' => 'Excepción comunicando con Verifactu/AEAT.',
            'error'   => $e->getMessage(),
        ];
    }
}
