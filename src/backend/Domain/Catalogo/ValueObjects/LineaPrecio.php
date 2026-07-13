<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\ValueObjects;

final class LineaPrecio
{
    public function __construct(
        public readonly string $codigo,
        public readonly string $descripcion,
        public readonly float $cantidad,
        public readonly float $ivaPercent,
        public readonly float $base,
        public readonly float $iva,
        public readonly float $total,
    ) {}

    public static function desdeTotalConIva(
        string $codigo,
        string $descripcion,
        float $cantidad,
        float $totalConIva,
        float $ivaPercent,
    ): self {
        $base = round($totalConIva / (1 + $ivaPercent / 100), 2);
        $iva = round($totalConIva - $base, 2);
        $total = round($base + $iva, 2);

        return new self(
            $codigo,
            $descripcion,
            $cantidad,
            $ivaPercent,
            $base,
            $iva,
            $total,
        );
    }

    public static function desdeBaseSinIva(
        string $codigo,
        string $descripcion,
        float $cantidad,
        float $baseSinIva,
        float $ivaPercent,
    ): self {
        $base = round($baseSinIva, 2);
        $iva = round($base * ($ivaPercent / 100), 2);
        $total = round($base + $iva, 2);

        return new self(
            $codigo,
            $descripcion,
            $cantidad,
            $ivaPercent,
            $base,
            $iva,
            $total,
        );
    }

    public function conBase(float $nuevaBase): self
    {
        $nuevoIva = round($nuevaBase * ($this->ivaPercent / 100), 2);
        $nuevoTotal = round($nuevaBase + $nuevoIva, 2);

        return new self(
            codigo: $this->codigo,
            descripcion: $this->descripcion,
            cantidad: $this->cantidad,
            ivaPercent: $this->ivaPercent,
            base: round($nuevaBase, 2),
            iva: $nuevoIva,
            total: $nuevoTotal,
        );
    }
}
