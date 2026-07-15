<?php

declare(strict_types=1);

namespace App\Domain\Reserva\ValueObjects;

final class ReservaServicioLinea
{
    public function __construct(
        public readonly int $servicioId,
        public readonly string $descripcion,
        public readonly float $cantidad,
        public readonly float $precioUnitario,
        public readonly float $impuestoPercent,
        public readonly float $totalBase,
        public readonly float $totalImpuesto,
        public readonly float $totalLinea,
    ) {}
}
