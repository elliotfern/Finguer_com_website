<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Entity;

use App\Domain\Catalogo\Enums\ModoPrecio;
use App\Domain\Catalogo\Enums\TipoServicio;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;

final class Servicio
{
    private function __construct(
        private readonly int $id,
        private readonly CodigoServicio $codigo,
        private readonly string $nombre,
        private readonly TipoServicio $tipo,
        private readonly ModoPrecio $modoPrecio,
        private readonly float $ivaPercent,
        private readonly bool $activo,
        // Campos para modo FIJO (parking)
        private readonly ?float $precioBase,
        private readonly ?int $diasIncluidos,
        private readonly ?float $minConIva,
        private readonly ?float $extraDiaConIva,
        // Campos para modo PORCENTAJE_CONDICIONAL (seguro)
        private readonly ?float $segUmbralConIva,
        private readonly ?float $segMinConIva,
        private readonly ?float $segFactor,
    ) {}

    public static function fromDatabase(array $row): self
    {
        return new self(
            id: (int) $row['id'],
            codigo: CodigoServicio::fromString($row['codigo']),
            nombre: $row['nombre'],
            tipo: TipoServicio::from($row['tipo']),
            modoPrecio: ModoPrecio::from($row['modo_precio']),
            ivaPercent: (float) $row['iva_percent'],
            activo: (bool) $row['activo'],
            precioBase: isset($row['precio_base'])
                ? (float) $row['precio_base']
                : null,
            diasIncluidos: isset($row['dias_incluidos'])
                ? (int) $row['dias_incluidos']
                : null,
            minConIva: isset($row['min_con_iva'])
                ? (float) $row['min_con_iva']
                : null,
            extraDiaConIva: isset($row['extra_dia_con_iva'])
                ? (float) $row['extra_dia_con_iva']
                : null,
            segUmbralConIva: isset($row['seg_umbral_con_iva'])
                ? (float) $row['seg_umbral_con_iva']
                : null,
            segMinConIva: isset($row['seg_min_con_iva'])
                ? (float) $row['seg_min_con_iva']
                : null,
            segFactor: isset($row['seg_factor'])
                ? (float) $row['seg_factor']
                : null,
        );
    }

    public function id(): int
    {
        return $this->id;
    }
    public function codigo(): CodigoServicio
    {
        return $this->codigo;
    }
    public function nombre(): string
    {
        return $this->nombre;
    }
    public function tipo(): TipoServicio
    {
        return $this->tipo;
    }
    public function modoPrecio(): ModoPrecio
    {
        return $this->modoPrecio;
    }
    public function ivaPercent(): float
    {
        return $this->ivaPercent;
    }
    public function activo(): bool
    {
        return $this->activo;
    }
    public function precioBase(): ?float
    {
        return $this->precioBase;
    }
    public function diasIncluidos(): ?int
    {
        return $this->diasIncluidos;
    }
    public function minConIva(): ?float
    {
        return $this->minConIva;
    }
    public function extraDiaConIva(): ?float
    {
        return $this->extraDiaConIva;
    }
    public function segUmbralConIva(): ?float
    {
        return $this->segUmbralConIva;
    }
    public function segMinConIva(): ?float
    {
        return $this->segMinConIva;
    }
    public function segFactor(): ?float
    {
        return $this->segFactor;
    }

    public function esParking(): bool
    {
        return $this->tipo === TipoServicio::Parking;
    }

    public function esExtra(): bool
    {
        return $this->tipo === TipoServicio::Extra;
    }

    public function esSeguro(): bool
    {
        return $this->tipo === TipoServicio::Seguro;
    }

    public function calcularPrecioExtra(): ?float
    {
        if (!$this->esExtra() || $this->precioBase === null) {
            return null;
        }
        return $this->precioBase;
    }

    public function calcularPrecioParking(int $dias): ?float
    {
        if (!$this->esParking() || $this->minConIva === null) {
            return null;
        }

        $diasIncluidos = $this->diasIncluidos ?? 10;
        $extraDia = $this->extraDiaConIva ?? 0.0;
        $diasExtra = max(0, $dias - $diasIncluidos);

        return round($this->minConIva + $diasExtra * $extraDia, 2);
    }

    public function calcularPrecioSeguro(float $totalConIva): ?float
    {
        if (!$this->esSeguro()) {
            return null;
        }

        $umbral = $this->segUmbralConIva ?? 0.0;
        $minimo = $this->segMinConIva ?? 0.0;
        $factor = $this->segFactor ?? 0.0;

        if ($totalConIva < $umbral) {
            return null; // No aplica
        }

        return round(max($minimo, $totalConIva * $factor), 2);
    }
}
