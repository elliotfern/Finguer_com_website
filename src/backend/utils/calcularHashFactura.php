<?php

/**
 * Calcula el hash interno de una factura a partir de sus datos
 * y del hash de la factura anterior.
 *
 * @param array  $row          Fila de la factura (de la BD)
 * @param string $hashAnterior Hash interno de la factura anterior ('' si es la primera)
 *
 * @return string              Hash SHA-256 en hexadecimal (64 caracteres)
 */
function calcularHashInternoFacturaFromRow(array $row, string $hashAnterior = ''): string
{
    $serie        = (string)($row['serie']         ?? '');
    $numero       = (string)($row['numero']        ?? '');
    $fechaEmision = (string)($row['fecha_emision'] ?? '');

    $subtotal      = isset($row['subtotal'])        ? (float)$row['subtotal']        : 0.0;
    $impuestoTotal = isset($row['impuesto_total'])  ? (float)$row['impuesto_total']  : 0.0;
    $total         = isset($row['total'])           ? (float)$row['total']           : 0.0;

    $nif           = (string)($row['facturar_a_nif'] ?? '');

    // Normalizamos números con 2 decimales, punto como separador
    $subtotalStr      = number_format($subtotal, 2, '.', '');
    $impuestoTotalStr = number_format($impuestoTotal, 2, '.', '');
    $totalStr         = number_format($total, 2, '.', '');

    // Cadena a hashear (determinista)
    $cadena = implode('|', [
        $serie,
        $numero,
        $fechaEmision,
        $subtotalStr,
        $impuestoTotalStr,
        $totalStr,
        $nif,
        $hashAnterior, // encadenamiento
    ]);

    return hash('sha256', $cadena);
}

/**
 * Verifica la integritat de la cadena de factures usant hash_interno i hash_interno_anterior.
 *
 * Retorna un array amb:
 * - status: 'ok' o 'error'
 * - total_facturas: int
 * - facturas_corruptas: int
 * - issues: llista d'errors trobats
 */
function verificarIntegridadFacturas(PDO $conn): array
{
    // 1) Recuperem totes les factures ordenades cronològicament
    $sql = "
        SELECT
            id,
            serie,
            numero,
            fecha_emision,
            subtotal,
            impuesto_total,
            total,
            facturar_a_nif,
            hash_interno,
            hash_interno_anterior
        FROM epgylzqu_parking_finguer_v2.facturas
        ORDER BY fecha_emision ASC, id ASC
    ";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $facturas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalFacturas = count($facturas);
    $issues = [];

    if ($totalFacturas === 0) {
        return [
            'status'            => 'ok',
            'total_facturas'    => 0,
            'facturas_corruptas' => 0,
            'issues'            => [],
            'message'           => 'No hi ha factures a verificar.',
        ];
    }

    $hashAnteriorEsperat = '';
    $index = 0;

    foreach ($facturas as $row) {
        $index++;
        $id        = (int)$row['id'];
        $serie     = (string)$row['serie'];
        $numero    = (string)$row['numero'];

        $hashGuardado         = $row['hash_interno'] ?? null;
        $hashAnteriorGuardado = $row['hash_interno_anterior'] ?? null;

        // Si no tenim hash_interno, ja és un problema
        if (empty($hashGuardado)) {
            $issues[] = [
                'factura_id' => $id,
                'serie'      => $serie,
                'numero'     => $numero,
                'motivo'     => 'hash_interno buit o NULL',
                'posicion'   => $index,
            ];
            // Encara així, continuem la cadena assumint hashAnteriorEsperat sense canvi
            continue;
        }

        // 2) Recalcular hash esperat a partir de les dades actuals + hash anterior esperat
        $hashEsperat = calcularHashInternoFacturaFromRow($row, $hashAnteriorEsperat);

        if (!hashesIguales($hashEsperat, (string)$hashGuardado)) {
            $issues[] = [
                'factura_id'     => $id,
                'serie'          => $serie,
                'numero'         => $numero,
                'motivo'         => 'hash_interno no coincideix amb el valor recalculat',
                'hash_guardado'  => (string)$hashGuardado,
                'hash_esperat'   => $hashEsperat,
                'posicion'       => $index,
            ];
        }

        // 3) Verificar encadenament amb la factura anterior
        $hashAnteriorGuardadoStr = $hashAnteriorGuardado !== null ? (string)$hashAnteriorGuardado : '';

        // Primera factura: esperem hash_anterior NULL o buit
        $hashAnteriorEsperatStr = $hashAnteriorEsperat !== '' ? $hashAnteriorEsperat : '';

        if ($index === 1) {
            // Primera de la cadena
            if ($hashAnteriorGuardadoStr !== '' && $hashAnteriorGuardadoStr !== null) {
                $issues[] = [
                    'factura_id'   => $id,
                    'serie'        => $serie,
                    'numero'       => $numero,
                    'motivo'       => 'Primera factura amb hash_interno_anterior no buit',
                    'hash_anterior_guardado' => $hashAnteriorGuardadoStr,
                    'posicion'     => $index,
                ];
            }
        } else {
            // A partir de la segona, el hash_interno_anterior ha de coincidir amb el hash de la factura anterior
            if (!hashesIguales($hashAnteriorGuardadoStr, $hashAnteriorEsperatStr)) {
                $issues[] = [
                    'factura_id'   => $id,
                    'serie'        => $serie,
                    'numero'       => $numero,
                    'motivo'       => 'hash_interno_anterior no coincideix amb el hash_interno de la factura anterior en la cadena',
                    'hash_anterior_guardado' => $hashAnteriorGuardadoStr,
                    'hash_anterior_esperat'  => $hashAnteriorEsperatStr,
                    'posicion'     => $index,
                ];
            }
        }

        // 4) Actualitzar el hash anterior esperat per a la següent factura
        // Usem el hashEsperat (recalculat) per propagar la cadena teòrica
        $hashAnteriorEsperat = $hashEsperat;
    }

    $corruptas = count($issues);

    return [
        'status'            => $corruptas === 0 ? 'ok' : 'error',
        'total_facturas'    => $totalFacturas,
        'facturas_corruptas' => $corruptas,
        'issues'            => $issues,
    ];
}

/**
 * Petit helper per comparar hashes (per si en algun moment volem normalitzar majúscules/minúscules).
 */
function hashesIguales(string $a, string $b): bool
{
    return hash_equals(strtolower($a), strtolower($b));
}
