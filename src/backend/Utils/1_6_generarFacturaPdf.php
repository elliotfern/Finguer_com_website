<?php

use Dompdf\Dompdf;
use Dompdf\Options;

function generarFacturaPdf(int $idFactura, array $opts = []): array
{
    global $conn;

    $idFactura = (int) $idFactura;

    $BASE_DIR = $_ENV['APP_BASE_DIR'] ?? '/home/epgylzqu/finguer.com/public';

    // Defaults opts
    $opts = array_merge(
        [
            'mode' => 'F',
            'force' => false,
            'base_dir' => $BASE_DIR,
            'subdir' => '/pdf/facturas',
        ],
        $opts,
    );

    // 1) Datos factura + reserva
    $sql = "
        SELECT
            f.id,
            f.numero,
            f.serie,
            f.fecha_emision,
            f.subtotal,
            f.total,
            f.impuesto_total,

            f.facturar_a_nombre,
            f.facturar_a_empresa,
            f.facturar_a_nif,
            f.facturar_a_direccion,
            f.facturar_a_ciudad,
            f.facturar_a_cp,
            f.facturar_a_pais,
            f.facturar_a_email,

            f.emisor_nombre_legal,
            f.emisor_nif,
            f.emisor_direccion,
            f.emisor_cp,
            f.emisor_ciudad,
            f.emisor_pais,

            pr.id              AS reserva_id,
            pr.entrada_prevista,
            pr.salida_prevista,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.tipo

        FROM facturas f
        JOIN parking_reservas pr ON f.reserva_id = pr.id
        WHERE f.id = :id
        LIMIT 1
    ";

    /** @var PDO $conn */
    $stmt = $conn->prepare($sql);
    $stmt->execute([':id' => $idFactura]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        return [
            'status' => 'error',
            'message' => 'Factura no encontrada',
            'code' => 'NOT_FOUND',
            'id' => $idFactura,
        ];
    }

    // 2) Líneas
    $sqlLines = "
        SELECT
            linea,
            descripcion,
            cantidad,
            precio_unitario,
            impuesto_percent,
            total_base,
            total_impuesto,
            total_linea
        FROM facturas_lineas
        WHERE factura_id = :fid
        ORDER BY linea ASC
    ";
    $stL = $conn->prepare($sqlLines);
    $stL->execute([':fid' => $idFactura]);
    $lineas = $stL->fetchAll(PDO::FETCH_ASSOC);

    // 3) Pago
    $sqlPago = "
        SELECT fecha, metodo, pasarela, estado, codigo_autorizacion, id_transaccion
        FROM pagos
        WHERE factura_id = :fid
        ORDER BY fecha DESC, id DESC
        LIMIT 1
    ";
    $stP = $conn->prepare($sqlPago);
    $stP->execute([':fid' => $idFactura]);
    $pago = $stP->fetch(PDO::FETCH_ASSOC);

    // --------
    // Variables / formatos
    // --------

    $numeroFactura = (string) $row['numero'];
    $serieFactura = (string) $row['serie'];

    $fechaEmisionFmt = date(
        'd-m-Y H:i:s',
        strtotime((string) $row['fecha_emision']),
    );

    $entradaPrevista = (string) ($row['entrada_prevista'] ?? '');
    $salidaPrevista = (string) ($row['salida_prevista'] ?? '');

    $fechaEntrada = $entradaPrevista
        ? date('d-m-Y', strtotime($entradaPrevista))
        : '-';
    $horaEntrada = $entradaPrevista
        ? date('H:i', strtotime($entradaPrevista))
        : '-';
    $fechaSalida = $salidaPrevista
        ? date('d-m-Y', strtotime($salidaPrevista))
        : '-';
    $horaSalida = $salidaPrevista
        ? date('H:i', strtotime($salidaPrevista))
        : '-';

    $vehiculo = (string) ($row['vehiculo'] ?? '');
    $matricula = (string) ($row['matricula'] ?? '');
    $tipo = (int) ($row['tipo'] ?? 0);

    $tipoReserva = $tipo === 2 ? 'Gold Finguer Class' : 'Finguer Class';

    $cliNombre = trim((string) ($row['facturar_a_nombre'] ?? ''));
    $cliEmail = trim((string) ($row['facturar_a_email'] ?? ''));
    $cliEmpresa = (string) ($row['facturar_a_empresa'] ?? '');
    $cliNif = (string) ($row['facturar_a_nif'] ?? '');
    $cliDir = (string) ($row['facturar_a_direccion'] ?? '');
    $cliCiudad = (string) ($row['facturar_a_ciudad'] ?? '');
    $cliCp = (string) ($row['facturar_a_cp'] ?? '');
    $cliPais = (string) ($row['facturar_a_pais'] ?? '');

    $emiNombre = trim((string) ($row['emisor_nombre_legal'] ?? ''));
    $emiNif = trim((string) ($row['emisor_nif'] ?? ''));
    $emiDir = trim((string) ($row['emisor_direccion'] ?? ''));
    $emiCp = trim((string) ($row['emisor_cp'] ?? ''));
    $emiCiudad = trim((string) ($row['emisor_ciudad'] ?? ''));
    $emiPais = trim((string) ($row['emisor_pais'] ?? ''));

    $subtotalFactura = (float) ($row['subtotal'] ?? 0);
    $ivaFactura = (float) ($row['impuesto_total'] ?? 0);
    $totalFactura = (float) ($row['total'] ?? 0);

    // Detección de descuadre (igual que antes)
    $sumTotL = 0.0;
    foreach ($lineas as $ln) {
        $sumTotL += (float) ($ln['total_linea'] ?? 0);
    }
    $diffTot = round($totalFactura - $sumTotL, 2);
    if (abs($diffTot) >= 0.01) {
        error_log(
            '[FINGUER] generarFacturaPdf WARNING: descuadre lineas vs factura (factura_id=' .
                $idFactura .
                ', total_factura=' .
                $totalFactura .
                ', total_lineas=' .
                round($sumTotL, 2) .
                ', diff=' .
                $diffTot .
                ')',
        );
    }

    // --------
    // Construir filas cliente / emisor
    // --------

    $cliRows = [];
    $cliRows[] = '<strong>Facturado a:</strong>';
    $cliRows[] = htmlspecialchars($cliNombre);
    $cliRows[] = htmlspecialchars($cliEmail);
    if (!empty($cliEmpresa)) {
        $cliRows[] = htmlspecialchars($cliEmpresa);
    }
    if (!empty($cliNif)) {
        $cliRows[] = 'NIF/NIE/CIF: ' . htmlspecialchars($cliNif);
    }
    if (!empty($cliDir)) {
        $cliRows[] = htmlspecialchars($cliDir);
    }
    $ciudadCp = trim(
        $cliCiudad . ($cliCiudad !== '' && $cliCp !== '' ? ', ' : '') . $cliCp,
    );
    if ($ciudadCp !== '') {
        $cliRows[] = htmlspecialchars($ciudadCp);
    }
    if (!empty($cliPais)) {
        $cliRows[] = htmlspecialchars($cliPais);
    }

    $emiRows = [];
    $emiRows[] = '<strong>' . htmlspecialchars($emiNombre) . '</strong>';
    if (!empty($emiNif)) {
        $emiRows[] = 'CIF/NIF: ' . htmlspecialchars($emiNif);
    }
    if (!empty($emiDir)) {
        $emiRows[] = htmlspecialchars($emiDir);
    }
    $emiCiudadCp = trim(
        $emiCiudad . ($emiCiudad !== '' && $emiCp !== '' ? ', ' : '') . $emiCp,
    );
    if ($emiCiudadCp !== '') {
        $emiRows[] = htmlspecialchars($emiCiudadCp);
    }
    if (!empty($emiPais)) {
        $emiRows[] = htmlspecialchars($emiPais);
    }

    $renderRows = function (array $rows): string {
        return implode('<br>', $rows);
    };

    // --------
    // Filas de líneas
    // --------

    $lineasHtml = '';
    if (!$lineas) {
        $lineasHtml = '<tr><td colspan="6">No hay líneas de factura.</td></tr>';
    } else {
        foreach ($lineas as $ln) {
            $desc = htmlspecialchars((string) ($ln['descripcion'] ?? ''));
            $cantRaw = (float) ($ln['cantidad'] ?? 0);
            $cantTxt =
                abs($cantRaw - round($cantRaw)) < 0.00001
                    ? (string) (int) round($cantRaw)
                    : rtrim(
                        rtrim(number_format($cantRaw, 2, ',', ''), '0'),
                        ',',
                    );
            $ivaP = (float) ($ln['impuesto_percent'] ?? 21);
            $base = (float) ($ln['total_base'] ?? 0);
            $ivaE = (float) ($ln['total_impuesto'] ?? 0);
            $totL = (float) ($ln['total_linea'] ?? 0);

            $lineasHtml .=
                '
                <tr>
                    <td class="col-desc">' .
                $desc .
                '</td>
                    <td class="col-num">' .
                $cantTxt .
                '</td>
                    <td class="col-num">' .
                number_format($base, 2, ',', '') .
                ' €</td>
                    <td class="col-num">' .
                number_format($ivaP, 2, ',', '') .
                '%</td>
                    <td class="col-num">' .
                number_format($ivaE, 2, ',', '') .
                ' €</td>
                    <td class="col-num"><strong>' .
                number_format($totL, 2, ',', '') .
                ' €</strong></td>
                </tr>
            ';
        }
    }

    // --------
    // Bloque pago
    // --------

    $pagoHtml = '';
    if ($pago) {
        $metodo = trim((string) ($pago['metodo'] ?? ''));
        $pasarela = trim((string) ($pago['pasarela'] ?? ''));
        $fechaPagoFmt = !empty($pago['fecha'])
            ? date('d-m-Y H:i:s', strtotime((string) $pago['fecha']))
            : '';

        $pagoHtml =
            '<div class="pago">
            <strong>Información del pago:</strong><br>' .
            ($metodo
                ? 'Pago mediante: ' . htmlspecialchars($metodo) . '<br>'
                : '') .
            ($pasarela
                ? 'Pasarela: ' . htmlspecialchars($pasarela) . '<br>'
                : '') .
            ($fechaPagoFmt
                ? 'Fecha de pago: ' . htmlspecialchars($fechaPagoFmt) . '<br>'
                : '') .
            '</div>';
    }

    // ===== HTML =====

    $filename = sprintf('factura_%s_%s.pdf', $serieFactura, $numeroFactura);

    $html =
        '<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
    @page {
        margin: 15mm 12mm 15mm 12mm;
    }
    * {
        box-sizing: border-box;
    }
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        line-height: 1.3;
        color: #000;
        margin: 0;
        padding: 0;
    }
    .header {
        width: 100%;
        margin-bottom: 12px;
    }
    .header img {
        height: 50px;
        width: auto;
    }
    .header-meta {
        margin-top: 6px;
        font-size: 12px;
    }
    table.partes {
        width: 100%;
        margin-bottom: 10px;
    }
    table.partes td {
        vertical-align: top;
        font-size: 12px;
        line-height: 1.4;
        padding: 0;
    }
    .reserva {
        font-size: 12px;
        margin-bottom: 12px;
        line-height: 1.5;
    }
    .titulo-seccion {
        text-align: center;
        font-size: 13px;
        font-weight: bold;
        margin: 14px 0;
    }
    table.lineas {
        width: 100%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 8px;
    }
    table.lineas th,
    table.lineas td {
        border: 1px solid #000;
        padding: 4px 5px;
        line-height: 1.3;
    }
    table.lineas thead tr {
        background-color: #000;
        color: #fff;
    }
    table.lineas thead th {
        border-color: #000;
    }
    .col-desc { width: 46%; }
    .col-num  { width: 10.8%; text-align: right; }
    table.totales {
        width: 55%;
        margin-left: 45%;
        border-collapse: collapse;
        font-size: 12px;
        margin-bottom: 8px;
    }
    table.totales td {
        border: 1px solid #000;
        padding: 4px 5px;
    }
    table.totales td:last-child {
        text-align: right;
    }
    .pago {
        font-size: 12px;
        margin-top: 14px;
        line-height: 1.5;
    }
</style>
</head>
<body>

<div class="header">
    <img src="https://finguer.com/img/logo-finguer.png" alt="Finguer">
    <div class="header-meta">
        <strong>Número de factura: ' .
        htmlspecialchars($numeroFactura) .
        '/' .
        htmlspecialchars($serieFactura) .
        '</strong><br>
        Fecha de la factura: ' .
        htmlspecialchars($fechaEmisionFmt) .
        '
    </div>
</div>

<table class="partes">
    <tr>
        <td style="width:55%;">' .
        $renderRows($cliRows) .
        '</td>
        <td style="width:45%; text-align:right;">' .
        $renderRows($emiRows) .
        '</td>
    </tr>
</table>

<div class="reserva">
    <strong>Resumen de la reserva</strong><br>
    Tipo de servicio: ' .
        htmlspecialchars($tipoReserva) .
        '<br>
    Entrada: ' .
        htmlspecialchars($fechaEntrada) .
        ' ' .
        htmlspecialchars($horaEntrada) .
        '<br>
    Salida: ' .
        htmlspecialchars($fechaSalida) .
        ' ' .
        htmlspecialchars($horaSalida) .
        '<br>
    Vehículo: ' .
        htmlspecialchars($vehiculo) .
        ' - Matrícula: ' .
        htmlspecialchars($matricula) .
        '
</div>

<div class="titulo-seccion">DETALLES DE LA FACTURA</div>

<table class="lineas">
    <thead>
        <tr>
            <th class="col-desc">Concepto</th>
            <th class="col-num">Cant.</th>
            <th class="col-num">Base</th>
            <th class="col-num">% IVA</th>
            <th class="col-num">IVA</th>
            <th class="col-num">Total</th>
        </tr>
    </thead>
    <tbody>
        ' .
        $lineasHtml .
        '
    </tbody>
</table>

<table class="totales">
    <tr>
        <td>Subtotal (Base imponible)</td>
        <td>' .
        number_format($subtotalFactura, 2, ',', '') .
        ' €</td>
    </tr>
    <tr>
        <td>IVA 21%</td>
        <td>' .
        number_format($ivaFactura, 2, ',', '') .
        ' €</td>
    </tr>
    <tr>
        <td><strong>Total</strong></td>
        <td><strong>' .
        number_format($totalFactura, 2, ',', '') .
        ' €</strong></td>
    </tr>
</table>

' .
        $pagoHtml .
        '

</body>
</html>';

    // ===== Dompdf =====

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $options->set('isRemoteEnabled', true); // necesario para cargar el logo por URL
    $options->set('isHtml5ParserEnabled', true);

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html, 'UTF-8');
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $pdfString = $dompdf->output();

    // ===== MODO NAVEGADOR =====
    if (($opts['mode'] ?? 'F') === 'FD') {
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        echo $pdfString;

        return [
            'status' => 'success',
            'mode' => 'FD',
            'message' => 'PDF mostrado en navegador',
        ];
    }

    // ===== MODO FICHERO =====
    $dir =
        rtrim((string) $opts['base_dir'], '/') .
        '/' .
        trim((string) $opts['subdir'], '/');

    if (!is_dir($dir)) {
        if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
            return [
                'status' => 'error',
                'message' => 'No se pudo crear directorio de PDFs',
                'dir' => $dir,
            ];
        }
    }

    $path = $dir . '/' . $filename;

    // Idempotencia
    if (empty($opts['force']) && file_exists($path) && filesize($path) > 0) {
        return [
            'status' => 'success',
            'mode' => 'F',
            'message' => 'PDF ya existía',
            'path' => $path,
            'subdir' => '/' . trim((string) $opts['subdir'], '/') . '/',
            'filename' => $filename,
        ];
    }

    file_put_contents($path, $pdfString);

    if (!file_exists($path) || filesize($path) === 0) {
        return [
            'status' => 'error',
            'message' => 'PDF no generado o vacío',
            'path' => $path,
        ];
    }

    return [
        'status' => 'success',
        'mode' => 'F',
        'message' => 'PDF generado correctamente',
        'path' => $path,
        'subdir' => '/' . trim((string) $opts['subdir'], '/') . '/',
        'filename' => $filename,
    ];
}
