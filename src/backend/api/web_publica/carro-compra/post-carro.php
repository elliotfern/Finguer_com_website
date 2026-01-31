<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

// 1) Método
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit();
}

// 2) Conexión PDO (AJUSTA ESTO a tu proyecto)
global $conn;
/** @var PDO $conn */
if (!isset($conn) || !($conn instanceof PDO)) {
    // Si en tu proyecto usas DatabaseConnection::getConnection(), cámbialo aquí:
    // $conn = DatabaseConnection::getConnection();
    http_response_code(500);
    echo json_encode(['error' => 'DB connection not available']);
    exit();
}

function readInput(): array
{
    $ct = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($ct, 'application/json') !== false) {
        $raw = file_get_contents('php://input');
        $data = json_decode($raw ?: '{}', true);
        return is_array($data) ? $data : [];
    }
    // fallback form-data / x-www-form-urlencoded
    return $_POST ?? [];
}

function calcularTotalDiasReserva(string $fechaInicioStr, string $fechaFinStr, string $tz = 'Europe/Rome'): int
{
    $tzObj = new DateTimeZone($tz);
    $inicio = new DateTime($fechaInicioStr, $tzObj);
    $fin    = new DateTime($fechaFinStr, $tzObj);

    $diffSegundos = $fin->getTimestamp() - $inicio->getTimestamp();
    if ($diffSegundos < 0) return 0;

    $diffDias = (int)ceil($diffSegundos / 86400);
    //return $diffDias + 1; // igual que tu TS
    return $diffDias; // igual que tu TS
}


/**
 * Ajusta las líneas para que el total final quede en euros "bonitos" (xx,00),
 * modificando la base de una línea objetivo (por defecto la RESERVA_*).
 *
 * Requisitos:
 * - Todas las líneas deben tener iva_percent (mismo IVA para simplificar: 21% en tu caso).
 * - Cada línea tiene: codigo, base, iva, total, iva_percent
 */
function beautifyTotalToWholeEuro(array &$lineas, float $ivaPercent, array $targetCodes = ['RESERVA_FINGUER', 'RESERVA_FINGUER_GOLD']): void
{
    // calcular totales actuales
    $subtotal = 0.0;
    foreach ($lineas as $l) $subtotal += (float)$l['base'];

    $ivaTotal = round($subtotal * ($ivaPercent / 100), 2);
    $total    = round($subtotal + $ivaTotal, 2);

    // ya es bonito
    $cents = (int) round(($total - floor($total)) * 100);
    if ($cents === 0) return;

    // objetivo: bajar al euro exacto (xx,00). (si prefieres subir, te lo cambio)
    $targetTotal = floor($total); // e.g. 140.01 -> 140.00

    // localizar índice de línea ajustable
    $idx = null;
    foreach ($lineas as $i => $l) {
        if (in_array($l['codigo'] ?? '', $targetCodes, true)) {
            $idx = $i;
            break;
        }
    }
    if ($idx === null) {
        // fallback: primera línea
        $idx = 0;
    }

    // buscamos un delta de base (en céntimos) pequeño que haga cuadrar el total
    // normalmente con -0.01 basta.
    for ($d = -50; $d <= 50; $d++) {
        $delta = $d / 100; // euros
        $newSubtotal = round($subtotal + $delta, 2);
        $newIvaTotal = round($newSubtotal * ($ivaPercent / 100), 2);
        $newTotal    = round($newSubtotal + $newIvaTotal, 2);

        if (abs($newTotal - $targetTotal) < 0.00001) {
            // aplicar ajuste a la línea elegida
            $lineas[$idx]['base'] = round(((float)$lineas[$idx]['base']) + $delta, 2);
            $lineas[$idx]['iva']  = round(((float)$lineas[$idx]['base']) * ($ivaPercent / 100), 2);
            $lineas[$idx]['total'] = round(((float)$lineas[$idx]['base']) + (float)$lineas[$idx]['iva'], 2);
            return;
        }
    }

    // si no encontramos ajuste exacto (raro), no tocamos nada
}


/**
 * Convierte un total "con IVA" a (base, iva, total) conservando el total bonito.
 * ivaPercent ejemplo: 21.00
 */
function splitConIva(float $totalConIva, float $ivaPercent): array
{
    $base = round($totalConIva / (1 + $ivaPercent / 100), 2);
    $iva  = round($totalConIva - $base, 2); // para cuadrar exacto con el "bonito"
    $tot  = round($base + $iva, 2);
    return [$base, $iva, $tot];
}

/**
 * Línea desde base sin IVA: (base, iva, total)
 */
function fromBase(float $baseSinIva, float $ivaPercent): array
{
    $base = round($baseSinIva, 2);
    $iva  = round($base * ($ivaPercent / 100), 2);
    $tot  = round($base + $iva, 2);
    return [$base, $iva, $tot];
}

function badRequest(string $msg): void
{
    http_response_code(400);
    echo json_encode(['error' => $msg]);
    exit();
}

// 3) Leer input
$in = readInput();

$session = trim((string)($in['session'] ?? ''));
$tipoReserva = strtoupper(trim((string)($in['tipoReserva'] ?? '')));
$limpiezaCodigo = trim((string)($in['limpieza'] ?? '0'));
$seguroCancelacion = (int)($in['seguroCancelacion'] ?? 0);

// Recomendado: enviar ya fechas completas desde frontend
$fechaEntrada = trim((string)($in['fechaEntrada'] ?? ''));
$fechaSalida  = trim((string)($in['fechaSalida'] ?? ''));

if ($session === '') badRequest('Missing session');
if ($tipoReserva === '') badRequest('Missing tipoReserva');
if ($fechaEntrada === '' || $fechaSalida === '') badRequest('Missing fechas');

// Normaliza limpieza
if ($limpiezaCodigo === '' || $limpiezaCodigo === '0') {
    $limpiezaCodigo = '0';
}

// Seguro: en tu HTML usas 1=Sí, 2=No (antes). Lo normalizamos:
$seguroContratado = ($seguroCancelacion === 1);

// 4) Calcular días
try {
    $diasReserva = calcularTotalDiasReserva($fechaEntrada, $fechaSalida, 'Europe/Rome');
} catch (Throwable $e) {
    badRequest('Invalid fechas format. Use "YYYY-MM-DD HH:MM:SS"');
}

if ($diasReserva <= 0) badRequest('Invalid date range');

// 5) Cargar catálogo de servicios necesarios
$stmtCat = $conn->prepare("
    SELECT id, codigo, nombre, tipo, iva_percent,
           precio_base, dias_incluidos, min_con_iva, extra_dia_con_iva,
           modo_precio, seg_base, seg_umbral_con_iva, seg_min_con_iva, seg_factor
    FROM parking_servicios_catalogo
    WHERE activo = 1 AND codigo = :codigo
    LIMIT 1
");

$load = function (string $codigo) use ($stmtCat): ?array {
    $stmtCat->execute([':codigo' => $codigo]);
    $row = $stmtCat->fetch(PDO::FETCH_ASSOC);
    $stmtCat->closeCursor();
    return $row ?: null;
};

$tarifa = $load($tipoReserva);
if (!$tarifa) badRequest("Tipo de reserva no válido: {$tipoReserva}");

// Limpieza (si aplica)
$limpieza = null;
if ($limpiezaCodigo !== '0') {
    $limpieza = $load($limpiezaCodigo);
    if (!$limpieza) badRequest("Limpieza no válida: {$limpiezaCodigo}");
}

// Seguro (si aplica)
$seguro = null;
if ($seguroContratado) {
    $seguro = $load('SEGURO_CANCELACION');
    if (!$seguro) badRequest("Servicio SEGURO_CANCELACION no encontrado o inactivo");
}

// 6) Construir líneas
$lineas = [];

$ivaTarifa = (float)$tarifa['iva_percent'];
$diasIncluidos = (int)($tarifa['dias_incluidos'] ?? 0);
$minConIva = (float)($tarifa['min_con_iva'] ?? 0);
$extraDiaConIva = (float)($tarifa['extra_dia_con_iva'] ?? 0);

if ($minConIva <= 0 || $diasIncluidos <= 0) {
    badRequest("Tarifa {$tipoReserva} no configurada (min_con_iva / dias_incluidos)");
}

$extraConIva = 0.0;
if ($diasReserva > $diasIncluidos && $extraDiaConIva > 0) {
    $extraConIva = ($diasReserva - $diasIncluidos) * $extraDiaConIva;
}

$totalTarifaConIva = round($minConIva + $extraConIva, 2);
[$baseTarifa, $ivaTarifaImporte, $totalTarifa] = splitConIva($totalTarifaConIva, $ivaTarifa);

$lineas[] = [
    'codigo' => $tarifa['codigo'],
    'descripcion' => $tarifa['nombre'],
    'cantidad' => 1.00,
    'iva_percent' => (float)$tarifa['iva_percent'],
    'base' => $baseTarifa,
    'iva' => $ivaTarifaImporte,
    'total' => $totalTarifa,
];

// Limpieza (precio_base sin IVA)
if ($limpieza) {
    $ivaL = (float)$limpieza['iva_percent'];
    $precioBase = (float)($limpieza['precio_base'] ?? 0);
    if ($precioBase <= 0) badRequest("Limpieza {$limpiezaCodigo} sin precio_base configurado");

    [$baseL, $ivaLImp, $totalL] = fromBase($precioBase, $ivaL);

    $lineas[] = [
        'codigo' => $limpieza['codigo'],
        'descripcion' => $limpieza['nombre'],
        'cantidad' => 1.00,
        'iva_percent' => (float)$limpieza['iva_percent'],
        'base' => $baseL,
        'iva' => $ivaLImp,
        'total' => $totalL,
    ];
}

// Seguro condicional (tu regla)
if ($seguro) {
    $ivaS = (float)$seguro['iva_percent'];

    // Base para calcular el seguro: total con IVA ANTES del seguro
    $totalConIvaSinSeguro = 0.0;
    foreach ($lineas as $ln) $totalConIvaSinSeguro += (float)$ln['total'];
    $totalConIvaSinSeguro = round($totalConIvaSinSeguro, 2);

    // Regla EXACTA TS:
    // if (precioSubtotal <= 100) seguro=30 else seguro=precioSubtotal*0.1
    $umbral = (float)($seguro['seg_umbral_con_iva'] ?? 100.00);
    $minSeg = (float)($seguro['seg_min_con_iva'] ?? 30.00);
    $factor = (float)($seguro['seg_factor'] ?? 0.10);

    $seguroConIva = 0.0;
    if ($totalConIvaSinSeguro <= $umbral) {
        $seguroConIva = $minSeg;
    } else {
        $seguroConIva = round($totalConIvaSinSeguro * $factor, 2);
    }

    [$baseS, $ivaSImp, $totalS] = splitConIva($seguroConIva, $ivaS);

    $lineas[] = [
        'codigo' => $seguro['codigo'],
        'descripcion' => $seguro['nombre'],
        'cantidad' => 1.00,
        'iva_percent' => (float)$seguro['iva_percent'],
        'base' => $baseS,
        'iva' => $ivaSImp,
        'total' => $totalS,
    ];
}

// 7) Totales
$subtotal = 0.0;
$ivaTotal = 0.0;
$total = 0.0;

foreach ($lineas as $ln) {
    $subtotal += (float)$ln['base'];
    $ivaTotal += (float)$ln['iva'];
    $total    += (float)$ln['total'];
}

$subtotal = round($subtotal, 2);
$ivaTotal = round($ivaTotal, 2);
$total    = round($total, 2);

// ✅ 7.1) Ajuste para que el total quede "bonito" (xx,00)
beautifyTotalToWholeEuro($lineas, 21.00, ['RESERVA_FINGUER', 'RESERVA_FINGUER_GOLD']);

// ✅ 7.2) Recalcular totales DESPUÉS del ajuste (muy importante)
$subtotal = 0.0;
$ivaTotal = 0.0;
$total = 0.0;

foreach ($lineas as $ln) {
    $subtotal += (float)$ln['base'];
    $ivaTotal += (float)$ln['iva'];
    $total    += (float)$ln['total'];
}

$subtotal = round($subtotal, 2);
$ivaTotal = round($ivaTotal, 2);
$total    = round($total, 2);

// 8) Snapshot JSON + hash
$snapshot = [
    'moneda' => 'EUR',
    'diasReserva' => $diasReserva,
    'seleccion' => [
        'session' => $session,
        'tipoReserva' => $tipoReserva,
        'limpieza' => $limpiezaCodigo,
        'seguroCancelacion' => $seguroContratado ? 1 : 0,
        'fechaEntrada' => $fechaEntrada,
        'fechaSalida' => $fechaSalida,
    ],
    'lineas' => $lineas,
    'totales' => [
        'subtotal_sin_iva' => $subtotal,
        'iva_total' => $ivaTotal,
        'total_con_iva' => $total,
    ],
];

$lineasJson = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
if ($lineasJson === false) {
    http_response_code(500);
    echo json_encode(['error' => 'JSON encode failed']);
    exit();
}

$hash = hash('sha256', $lineasJson);

// 9) Guardar en carro_compra (UPSERT por session)
$stmtUpsert = $conn->prepare("
    INSERT INTO carro_compra
      (session, subtotal_sin_iva, iva_total, total_con_iva, lineas_json, hash)
    VALUES
      (:session, :subtotal, :iva_total, :total, :lineas_json, :hash)
    ON DUPLICATE KEY UPDATE
      subtotal_sin_iva = VALUES(subtotal_sin_iva),
      iva_total        = VALUES(iva_total),
      total_con_iva    = VALUES(total_con_iva),
      lineas_json      = VALUES(lineas_json),
      hash             = VALUES(hash),
      updated_at       = CURRENT_TIMESTAMP
");

$stmtUpsert->execute([
    ':session'    => $session,
    ':subtotal'   => $subtotal,
    ':iva_total'  => $ivaTotal,
    ':total'      => $total,
    ':lineas_json' => $lineasJson,
    ':hash'       => $hash,
]);

// 10) Respuesta
echo json_encode([
    'ok' => true,
    'diasReserva' => $diasReserva,
    'lineas' => $lineas,
    'subtotal' => $subtotal,
    'iva_total' => $ivaTotal,
    'total' => $total,
    'hash' => $hash,
]);
