<?php

function generarFacturaPdf(int $idFactura, array $opts = []): array
{
  global $conn;

  $idFactura = (int)$idFactura;

  require_once(APP_ROOT . '/vendor/tecnickcom/tcpdf/tcpdf.php');
  require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/Exception.php');
  require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/PHPMailer.php');
  require_once(APP_ROOT . '/vendor/phpmailer/phpmailer/src/SMTP.php');

  function fmtCantidad(float $x): string
  {
    return (abs($x - round($x)) < 0.00001) ? (string)(int)round($x) : rtrim(rtrim(number_format($x, 2, ',', ''), '0'), ',');
  }

  // Defaults opts
  $opts = array_merge([
    'mode'     => 'F',      // 'I' navegador, 'F' fichero
    'force'    => false,
    'base_dir' => APP_ROOT . '/storage',
    'subdir'   => '/factures',
  ], $opts);

  // 1) Datos factura (snapshot cliente + snapshot emisor) + reserva
  $sql = "
        SELECT
            f.id,
            f.numero,
            f.serie,
            f.fecha_emision,
            f.subtotal,
            f.total,
            f.impuesto_total,

            -- snapshot cliente (FACTURAS)
            f.facturar_a_nombre,
            f.facturar_a_empresa,
            f.facturar_a_nif,
            f.facturar_a_direccion,
            f.facturar_a_ciudad,
            f.facturar_a_cp,
            f.facturar_a_pais,
            f.facturar_a_email,

            -- snapshot emisor (FACTURAS)
            f.emisor_nombre_legal,
            f.emisor_nif,
            f.emisor_direccion,
            f.emisor_cp,
            f.emisor_ciudad,
            f.emisor_pais,

            -- reserva
            pr.id              AS reserva_id,
            pr.entrada_prevista,
            pr.salida_prevista,
            pr.vehiculo,
            pr.matricula,
            pr.vuelo,
            pr.tipo

        FROM epgylzqu_parking_finguer_v2.facturas f
        JOIN epgylzqu_parking_finguer_v2.parking_reservas pr
          ON f.reserva_id = pr.id
        WHERE f.id = :id
        LIMIT 1
    ";

  /** @var PDO $conn */
  $stmt = $conn->prepare($sql);
  $stmt->execute([':id' => $idFactura]);
  $row = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!$row) {
    return [
      'status'  => 'error',
      'message' => 'Factura no encontrada',
      'code'    => 'NOT_FOUND',
      'id'      => $idFactura,
    ];
  }

  // 2) Líneas desde facturas_lineas (snapshot real facturado)
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
        FROM epgylzqu_parking_finguer_v2.facturas_lineas
        WHERE factura_id = :fid
        ORDER BY linea ASC
    ";
  $stL = $conn->prepare($sqlLines);
  $stL->execute([':fid' => $idFactura]);
  $lineas = $stL->fetchAll(PDO::FETCH_ASSOC);

  // 3) Informacion sobre el pago
  $sqlPago = "
    SELECT fecha, metodo, pasarela, estado, codigo_autorizacion, id_transaccion
    FROM epgylzqu_parking_finguer_v2.pagos
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

  $numeroFactura = (string)$row['numero'];
  $serieFactura  = (string)$row['serie'];

  $fechaEmisionRaw = (string)$row['fecha_emision'];
  $fechaEmisionFmt = date('d-m-Y H:i:s', strtotime($fechaEmisionRaw));

  // Reserva: entrada/salida
  $entradaPrevista = (string)($row['entrada_prevista'] ?? '');
  $salidaPrevista  = (string)($row['salida_prevista'] ?? '');

  $fechaEntrada = $entradaPrevista ? date('d-m-Y', strtotime($entradaPrevista)) : '-';
  $horaEntrada  = $entradaPrevista ? date('H:i',   strtotime($entradaPrevista)) : '-';
  $fechaSalida  = $salidaPrevista  ? date('d-m-Y', strtotime($salidaPrevista))  : '-';
  $horaSalida   = $salidaPrevista  ? date('H:i',   strtotime($salidaPrevista))  : '-';

  $vehiculo  = (string)($row['vehiculo'] ?? '');
  $matricula = (string)($row['matricula'] ?? '');
  $tipo      = (int)($row['tipo'] ?? 0);

  $tipoReserva2 = ($tipo === 2) ? "Gold Finguer Class" : "Finguer Class";

  // Cliente (desde FACTURAS)
  $cliNombre  = trim((string)($row['facturar_a_nombre'] ?? ''));
  $cliEmail   = trim((string)($row['facturar_a_email'] ?? ''));
  $cliEmpresa = (string)($row['facturar_a_empresa'] ?? '');
  $cliNif     = (string)($row['facturar_a_nif'] ?? '');
  $cliDir     = (string)($row['facturar_a_direccion'] ?? '');
  $cliCiudad  = (string)($row['facturar_a_ciudad'] ?? '');
  $cliCp      = (string)($row['facturar_a_cp'] ?? '');
  $cliPais    = (string)($row['facturar_a_pais'] ?? '');

  // Emisor (desde FACTURAS)
  $emiNombre = trim((string)($row['emisor_nombre_legal'] ?? ''));
  $emiNif    = trim((string)($row['emisor_nif'] ?? ''));
  $emiDir    = trim((string)($row['emisor_direccion'] ?? ''));
  $emiCp     = trim((string)($row['emisor_cp'] ?? ''));
  $emiCiudad = trim((string)($row['emisor_ciudad'] ?? ''));
  $emiPais   = trim((string)($row['emisor_pais'] ?? ''));

  // ✅ Totales oficiales (SIN recalcular)
  $subtotalFactura = (float)($row['subtotal'] ?? 0);
  $ivaFactura      = (float)($row['impuesto_total'] ?? 0);
  $totalFactura    = (float)($row['total'] ?? 0);

  // (Opcional) Detectar descuadre de líneas vs totales y loguearlo
  $sumBaseL = 0.0;
  $sumIvaL = 0.0;
  $sumTotL = 0.0;
  foreach ($lineas as $ln) {
    $sumBaseL += (float)($ln['total_base'] ?? 0);
    $sumIvaL  += (float)($ln['total_impuesto'] ?? 0);
    $sumTotL  += (float)($ln['total_linea'] ?? 0);
  }
  $diffTot = round($totalFactura - $sumTotL, 2);
  if (abs($diffTot) >= 0.01) {
    error_log('[FINGUER] generarFacturaPdf WARNING: descuadre lineas vs factura (factura_id=' . $idFactura
      . ', total_factura=' . $totalFactura
      . ', total_lineas=' . round($sumTotL, 2)
      . ', diff=' . $diffTot . ')');
  }

  // ===== PDF =====
  $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8');
  $pdf->SetAutoPageBreak(true, 12);
  $pdf->SetMargins(12, 12, 12);
  $pdf->AddPage();

  $filename = sprintf('factura_%s_%s.pdf', $serieFactura, $numeroFactura);

  // Cabecera: logo + datos factura
  $htmlContent = '
    <div style="margin:0;font-size:10px;line-height:12px;">
        <div style="font-size:10px; line-height:12px;">
            <img alt="Finguer" src="https://finguer.com/public/img/logo-header.svg" width="150" height="70">
        </div>

        <div>
            <strong>Número de factura: ' . htmlspecialchars($numeroFactura) . '/' . htmlspecialchars($serieFactura) . '</strong><br>
            Fecha de la factura: ' . htmlspecialchars($fechaEmisionFmt) . '<br>
        </div>
    ';

  $htmlContent .= '<table width="100%" cellpadding="0" cellspacing="0" border="0"><tr><td style="padding-left:2px; padding-right:2px;">';

  // ==== CABECERA CLIENTE / EMISOR ====
  $rowsCliente = [];
  $rowsCliente[] = ['<strong>Facturado a:</strong>'];
  $rowsCliente[] = [htmlspecialchars($cliNombre)];
  $rowsCliente[] = [htmlspecialchars($cliEmail)];
  if (!empty($cliEmpresa)) $rowsCliente[] = [htmlspecialchars($cliEmpresa)];
  if (!empty($cliNif))     $rowsCliente[] = ['NIF/NIE/CIF: ' . htmlspecialchars($cliNif)];
  if (!empty($cliDir))     $rowsCliente[] = [htmlspecialchars($cliDir)];
  $ciudadCp = trim($cliCiudad . (($cliCiudad !== '' && $cliCp !== '') ? ', ' : '') . $cliCp);
  if ($ciudadCp !== '')    $rowsCliente[] = [htmlspecialchars($ciudadCp)];
  if (!empty($cliPais))    $rowsCliente[] = [htmlspecialchars($cliPais)];

  $rowsEmisor = [];
  $rowsEmisor[] = ['<strong>' . htmlspecialchars($emiNombre) . '</strong>'];
  if (!empty($emiNif))  $rowsEmisor[] = ['CIF/NIF: ' . htmlspecialchars($emiNif)];
  if (!empty($emiDir))  $rowsEmisor[] = [htmlspecialchars($emiDir)];
  $emiCiudadCp = trim($emiCiudad . (($emiCiudad !== '' && $emiCp !== '') ? ', ' : '') . $emiCp);
  if ($emiCiudadCp !== '') $rowsEmisor[] = [htmlspecialchars($emiCiudadCp)];
  if (!empty($emiPais)) $rowsEmisor[] = [htmlspecialchars($emiPais)];

  $renderRows = function (array $rows, string $align = 'left'): string {
    $out = '<table cellpadding="0" cellspacing="0" border="0" width="100%">';
    foreach ($rows as $r) {
      // ✅ 1px de separación real entre filas (sin inflar)
      $out .= '<tr><td align="' . $align . '" style="font-size:10px; line-height:11px; padding:0 0 1px 0;">'
        . $r[0]
        . '</td></tr>';
    }
    $out .= '</table>';
    return $out;
  };

  $htmlContent .= '
      <table cellpadding="2" cellspacing="0" border="0" width="100%" style="font-size:10px; line-height:11px;">
        <tr>
          <td width="55%" valign="top">' . $renderRows($rowsCliente, 'left') . '</td>
          <td width="45%" valign="top" align="right">' . $renderRows($rowsEmisor, 'right') . '</td>
        </tr>
      </table>
      <div style="height:6px;"></div>
    ';

  // Detalles reserva (no legal, pero útil)
  $htmlContent .= '
        <table cellpadding="4" cellspacing="0" border="0" width="100%">
          <tr>
            <td style="font-size:10px; line-height:12px;">
              <strong>Resumen de la reserva</strong><br>
              Tipo de servicio: ' . htmlspecialchars($tipoReserva2) . '<br>
              Entrada: ' . htmlspecialchars($fechaEntrada) . ' ' . htmlspecialchars($horaEntrada) . '<br>
              Salida: ' . htmlspecialchars($fechaSalida) . ' ' . htmlspecialchars($horaSalida) . '<br>
              Vehículo: ' . htmlspecialchars($vehiculo) . ' - Matrícula: ' . htmlspecialchars($matricula) . '
            </td>
          </tr>
        </table>
    ';

  // Título centrado
  $htmlContent .= '
        <div style="height:16px;"></div>
        <table width="100%" cellpadding="4" cellspacing="0" border="0">
          <tr>
            <td align="center" style="font-size:11px; line-height:13px;">
              <strong>DETALLES DE LA FACTURA</strong>
            </td>
          </tr>
        </table>
        <div style="height:16px;"></div>
    ';

  // Tabla líneas: ✅ SOLO valores BD (sin cálculos)
  $htmlContent .= '
        <table cellpadding="4" cellspacing="0" border="1" width="100%" style="font-size:10px; line-height:12px;">
          <tr style="background-color:#000;color:#fff;">
            <td width="46%"><strong>Concepto</strong></td>
            <td width="10%" align="right"><strong>Cant.</strong></td>
            <td width="14%" align="right"><strong>Base</strong></td>
            <td width="10%" align="right"><strong>% IVA</strong></td>
            <td width="10%" align="right"><strong>IVA</strong></td>
            <td width="10%" align="right"><strong>Total</strong></td>
          </tr>
    ';

  if (!$lineas) {
    $htmlContent .= '<tr><td colspan="6">No hay líneas de factura.</td></tr>';
  } else {
    foreach ($lineas as $ln) {
      $desc = (string)($ln['descripcion'] ?? '');
      $cantRaw = (float)($ln['cantidad'] ?? 0);
      $cantTxt = (abs($cantRaw - round($cantRaw)) < 0.00001)
        ? (string)(int)round($cantRaw)
        : rtrim(rtrim(number_format($cantRaw, 2, ',', ''), '0'), ',');
      $ivaP = (float)($ln['impuesto_percent'] ?? 21);

      $base = (float)($ln['total_base'] ?? 0);
      $ivaE = (float)($ln['total_impuesto'] ?? 0);
      $totL = (float)($ln['total_linea'] ?? 0);

      $htmlContent .= '
              <tr>
                <td width="46%">' . htmlspecialchars($desc) . '</td>
                <td width="10%" align="right">' . $cantTxt . '</td>
                <td width="14%" align="right">' . number_format($base, 2, ',', '') . ' €</td>
                <td width="10%" align="right">' . number_format($ivaP, 2, ',', '') . '%</td>
                <td width="10%" align="right">' . number_format($ivaE, 2, ',', '') . ' €</td>
                <td width="10%" align="right"><strong>' . number_format($totL, 2, ',', '') . ' €</strong></td>
              </tr>
            ';
    }
  }

  $htmlContent .= '</table>';

  // Totales: ✅ SOLO snapshot oficial FACTURAS
  $htmlContent .= '
        <div style="height:8px;"></div>
        <table width="100%" cellpadding="0" cellspacing="0" border="0">
          <tr>
            <td width="45%"></td>
            <td width="55%" align="right">
              <table cellpadding="4" cellspacing="0" border="1" width="100%" style="font-size:10px; line-height:12px;">
                <tr>
                  <td width="70%">Subtotal (Base imponible)</td>
                  <td width="30%" align="right">' . number_format($subtotalFactura, 2, ',', '') . ' €</td>
                </tr>
                <tr>
                  <td width="70%">IVA 21%</td>
                  <td width="30%" align="right">' . number_format($ivaFactura, 2, ',', '') . ' €</td>
                </tr>
                <tr>
                  <td width="70%"><strong>Total</strong></td>
                  <td width="30%" align="right"><strong>' . number_format($totalFactura, 2, ',', '') . ' €</strong></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        <div style="height:6px;"></div>
      ';

  if ($pago) {
    $metodo  = trim((string)($pago['metodo'] ?? ''));
    $pasarela = trim((string)($pago['pasarela'] ?? ''));
    $fechaPagoRaw = (string)($pago['fecha'] ?? '');
    $fechaPagoFmt = $fechaPagoRaw ? date('d-m-Y H:i:s', strtotime($fechaPagoRaw)) : '';

    $htmlContent .= '
    <div style="height:15px;"></div>
    <table width="100%" cellpadding="4" cellspacing="0" border="0">
      <tr>
        <td style="font-size:10px; line-height:12px;">
          <strong>Información del pago:</strong><br>
          ' . ($metodo ? 'Pago mediante: ' . htmlspecialchars($metodo) . '<br>' : '') . '
          ' . ($pasarela ? 'Pasarela: ' . htmlspecialchars($pasarela) . '<br>' : '') . '
          ' . ($fechaPagoFmt ? 'Fecha de pago: ' . htmlspecialchars($fechaPagoFmt) . '<br>' : '') . '
        </td>
      </tr>
    </table>
  ';
  }

  $htmlContent .= '</td></tr></table>';
  $htmlContent .= '</div>';

  $pdf->writeHTML($htmlContent, true, false, true, false, '');

  // ===== MODO NAVEGADOR (intranet) =====
  if (($opts['mode'] ?? 'F') === 'I') {
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $filename . '"');
    $pdf->Output($filename, 'I');

    return [
      'status'  => 'success',
      'mode'    => 'I',
      'message' => 'PDF mostrado en navegador',
    ];
  }

  // ===== MODO FICHERO (email / cron) =====
  $dir = rtrim((string)$opts['base_dir'], '/') . '/' . trim((string)$opts['subdir'], '/');

  if (!is_dir($dir)) {
    if (!mkdir($dir, 0755, true) && !is_dir($dir)) {
      return [
        'status'  => 'error',
        'message' => 'No se pudo crear directorio de PDFs',
        'dir'     => $dir,
      ];
    }
  }

  $path = $dir . '/' . $filename;

  // Idempotencia
  if (empty($opts['force']) && file_exists($path) && filesize($path) > 0) {
    return [
      'status'  => 'success',
      'mode'    => 'F',
      'message' => 'PDF ya existía',
      'path'    => $path,
    ];
  }

  $pdf->Output($path, 'F');

  if (!file_exists($path) || filesize($path) === 0) {
    return [
      'status'  => 'error',
      'message' => 'PDF no generado o vacío',
      'path'    => $path,
    ];
  }

  return [
    'status'  => 'success',
    'mode'    => 'F',
    'message' => 'PDF generado correctamente',
    'path'    => $path,
  ];
}
