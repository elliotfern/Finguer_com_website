<?php

function generarFacturaPdf(int $idFactura): void
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
    $id_old = (int)$row['numero'];

    // Fecha de emisión (antes fechaReserva)
    $fechaReserva_old = $row['fecha_emision'];
    $fechaReserva     = date('d-m-Y H:i:s', strtotime($fechaReserva_old));
    $fechaAnoReserva  = date('Y', strtotime($fechaReserva_old));

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
            <strong>Número de factura: ' . $id_old . '/' . $fechaAnoReserva . '</strong><br>
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
    $filename = sprintf('factura_%s_%s.pdf', $row['serie'], $id_old);
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    $pdf->Output($filename, 'I');
}
