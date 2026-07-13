<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Service;

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;

final class CalculadoraLineasReserva
{
    /**
     * Construye las líneas de precio de una reserva a partir de los
     * servicios ya cargados del catálogo, aplicando el ajuste a euro
     * redondo sobre la línea de tarifa principal.
     *
     * @return LineaPrecio[]
     */
    public static function calcular(
        Servicio $tarifa,
        int $dias,
        ?Servicio $limpieza = null,
        ?Servicio $seguro = null,
    ): array {
        $lineas = [];

        $precioTarifaConIva = $tarifa->calcularPrecioParking($dias);
        if ($precioTarifaConIva === null) {
            throw new \InvalidArgumentException(
                "El servicio {$tarifa->codigo()->value()} no es una tarifa de parking válida.",
            );
        }

        $lineas[] = LineaPrecio::desdeTotalConIva(
            codigo: $tarifa->codigo()->value(),
            descripcion: $tarifa->nombre(),
            cantidad: 1.0,
            totalConIva: $precioTarifaConIva,
            ivaPercent: $tarifa->ivaPercent(),
        );

        if ($limpieza !== null) {
            $precioLimpieza = $limpieza->calcularPrecioExtra();
            if ($precioLimpieza === null) {
                throw new \InvalidArgumentException(
                    "El servicio {$limpieza->codigo()->value()} no es un extra válido.",
                );
            }

            $lineas[] = LineaPrecio::desdeBaseSinIva(
                codigo: $limpieza->codigo()->value(),
                descripcion: $limpieza->nombre(),
                cantidad: 1.0,
                baseSinIva: $precioLimpieza,
                ivaPercent: $limpieza->ivaPercent(),
            );
        }

        if ($seguro !== null) {
            $totalConIvaSinSeguro = array_sum(
                array_map(fn(LineaPrecio $l) => $l->total, $lineas),
            );

            $precioSeguro = $seguro->calcularPrecioSeguro(
                round($totalConIvaSinSeguro, 2),
            );

            $lineas[] = LineaPrecio::desdeTotalConIva(
                codigo: $seguro->codigo()->value(),
                descripcion: $seguro->nombre(),
                cantidad: 1.0,
                totalConIva: $precioSeguro,
                ivaPercent: $seguro->ivaPercent(),
            );
        }

        return AjustadorTotalRedondo::ajustar($lineas);
    }
}
