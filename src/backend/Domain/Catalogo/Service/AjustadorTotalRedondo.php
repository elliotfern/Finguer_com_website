<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Service;

use App\Domain\Catalogo\ValueObjects\LineaPrecio;

final class AjustadorTotalRedondo
{
    /**
     * Ajusta la base de una línea objetivo para que el total final
     * de todas las líneas quede en euros exactos (xx,00), si es posible.
     *
     * @param LineaPrecio[] $lineas
     * @param string[] $codigosPrioritarios códigos preferidos para aplicar el ajuste
     * @return LineaPrecio[]
     */
    public static function ajustar(
        array $lineas,
        array $codigosPrioritarios = [
            'RESERVA_FINGUER',
            'RESERVA_FINGUER_GOLD',
        ],
    ): array {
        $subtotal = array_sum(
            array_map(fn(LineaPrecio $l) => $l->base, $lineas),
        );
        $total = array_sum(array_map(fn(LineaPrecio $l) => $l->total, $lineas));
        $total = round($total, 2);

        $cents = (int) round(($total - floor($total)) * 100);
        if ($cents === 0) {
            return $lineas; // ya es un total "bonito"
        }

        $targetTotal = floor($total);

        $idx = self::localizarIndiceObjetivo($lineas, $codigosPrioritarios);

        foreach (range(-50, 50) as $d) {
            $delta = $d / 100;
            $lineaAjustada = $lineas[$idx]->conBase(
                $lineas[$idx]->base + $delta,
            );

            $nuevoTotal = self::recalcularTotal($lineas, $idx, $lineaAjustada);

            if (abs($nuevoTotal - $targetTotal) < 0.00001) {
                $lineas[$idx] = $lineaAjustada;
                return $lineas;
            }
        }

        // No se encontró ajuste exacto: se devuelve sin modificar (igual que el legacy)
        return $lineas;
    }

    /**
     * @param LineaPrecio[] $lineas
     */
    private static function localizarIndiceObjetivo(
        array $lineas,
        array $codigosPrioritarios,
    ): int {
        foreach ($lineas as $i => $linea) {
            if (in_array($linea->codigo, $codigosPrioritarios, true)) {
                return $i;
            }
        }

        return array_key_first($lineas) ?? 0;
    }

    /**
     * @param LineaPrecio[] $lineas
     */
    private static function recalcularTotal(
        array $lineas,
        int $idxAjustado,
        LineaPrecio $lineaAjustada,
    ): float {
        $total = 0.0;
        foreach ($lineas as $i => $linea) {
            $total +=
                $i === $idxAjustado ? $lineaAjustada->total : $linea->total;
        }

        return round($total, 2);
    }
}
