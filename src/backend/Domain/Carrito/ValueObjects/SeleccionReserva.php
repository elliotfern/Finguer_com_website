<?php

declare(strict_types=1);

namespace App\Domain\Carrito\ValueObjects;

final class SeleccionReserva
{
    public function __construct(
        public readonly string $tipoReserva,
        public readonly string $limpiezaCodigo, // '0' si no se selecciona ninguna
        public readonly bool $seguroCancelacion,
        public readonly \DateTimeImmutable $fechaEntrada,
        public readonly \DateTimeImmutable $fechaSalida,
    ) {}

    public function tieneLimpieza(): bool
    {
        return $this->limpiezaCodigo !== '0';
    }
}
