<?php

declare(strict_types=1);

namespace App\Domain\Carrito\Entity;

use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;

final class Carrito
{
    /**
     * @param LineaPrecio[] $lineas
     */
    private function __construct(
        private readonly string $session,
        private readonly SeleccionReserva $seleccion,
        private readonly int $diasReserva,
        private readonly array $lineas,
        private readonly float $subtotalSinIva,
        private readonly float $ivaTotal,
        private readonly float $totalConIva,
        private readonly string $hash,
        private readonly ?\DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * @param LineaPrecio[] $lineas
     */
    public static function crear(
        string $session,
        SeleccionReserva $seleccion,
        int $diasReserva,
        array $lineas,
    ): self {
        $subtotal = round(
            array_sum(array_map(fn(LineaPrecio $l) => $l->base, $lineas)),
            2,
        );
        $ivaTotal = round(
            array_sum(array_map(fn(LineaPrecio $l) => $l->iva, $lineas)),
            2,
        );
        $total = round(
            array_sum(array_map(fn(LineaPrecio $l) => $l->total, $lineas)),
            2,
        );

        $instancia = new self(
            session: $session,
            seleccion: $seleccion,
            diasReserva: $diasReserva,
            lineas: $lineas,
            subtotalSinIva: $subtotal,
            ivaTotal: $ivaTotal,
            totalConIva: $total,
            hash: '',
        );

        $hash = hash('sha256', $instancia->toSnapshotJson());

        return new self(
            session: $session,
            seleccion: $seleccion,
            diasReserva: $diasReserva,
            lineas: $lineas,
            subtotalSinIva: $subtotal,
            ivaTotal: $ivaTotal,
            totalConIva: $total,
            hash: $hash,
        );
    }

    /**
     * @param LineaPrecio[] $lineas
     */
    public static function fromDatabase(
        string $session,
        SeleccionReserva $seleccion,
        int $diasReserva,
        array $lineas,
        float $subtotalSinIva,
        float $ivaTotal,
        float $totalConIva,
        string $hash,
        ?\DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $session,
            $seleccion,
            $diasReserva,
            $lineas,
            $subtotalSinIva,
            $ivaTotal,
            $totalConIva,
            $hash,
            $updatedAt,
        );
    }

    public function session(): string
    {
        return $this->session;
    }
    public function seleccion(): SeleccionReserva
    {
        return $this->seleccion;
    }
    public function diasReserva(): int
    {
        return $this->diasReserva;
    }
    /** @return LineaPrecio[] */
    public function lineas(): array
    {
        return $this->lineas;
    }
    public function subtotalSinIva(): float
    {
        return $this->subtotalSinIva;
    }
    public function ivaTotal(): float
    {
        return $this->ivaTotal;
    }
    public function totalConIva(): float
    {
        return $this->totalConIva;
    }
    public function hash(): string
    {
        return $this->hash;
    }
    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Estructura de snapshot pública, reutilizable por controladores
     * y por la propia entidad para calcular el hash / persistir.
     */
    public function toSnapshotArray(): array
    {
        return [
            'moneda' => 'EUR',
            'diasReserva' => $this->diasReserva,
            'seleccion' => [
                'session' => $this->session,
                'tipoReserva' => $this->seleccion->tipoReserva,
                'limpieza' => $this->seleccion->limpiezaCodigo,
                'seguroCancelacion' => $this->seleccion->seguroCancelacion
                    ? 1
                    : 0,
                'fechaEntrada' => $this->seleccion->fechaEntrada->format(
                    'Y-m-d H:i:s',
                ),
                'fechaSalida' => $this->seleccion->fechaSalida->format(
                    'Y-m-d H:i:s',
                ),
            ],
            'lineas' => array_map(
                fn(LineaPrecio $l) => [
                    'codigo' => $l->codigo,
                    'descripcion' => $l->descripcion,
                    'cantidad' => $l->cantidad,
                    'iva_percent' => $l->ivaPercent,
                    'base' => $l->base,
                    'iva' => $l->iva,
                    'total' => $l->total,
                ],
                $this->lineas,
            ),
            'totales' => [
                'subtotal_sin_iva' => $this->subtotalSinIva,
                'iva_total' => $this->ivaTotal,
                'total_con_iva' => $this->totalConIva,
            ],
        ];
    }

    public function toSnapshotJson(): string
    {
        return (string) json_encode(
            $this->toSnapshotArray(),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }

    public function lineasJson(): string
    {
        return $this->toSnapshotJson();
    }
}
