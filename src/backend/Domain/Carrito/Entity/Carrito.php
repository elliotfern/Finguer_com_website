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

        $snapshot = self::construirSnapshot(
            $session,
            $seleccion,
            $diasReserva,
            $lineas,
            $subtotal,
            $ivaTotal,
            $total,
        );

        $hash = hash('sha256', $snapshot);

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

    public function lineasJson(): string
    {
        return self::construirSnapshot(
            $this->session,
            $this->seleccion,
            $this->diasReserva,
            $this->lineas,
            $this->subtotalSinIva,
            $this->ivaTotal,
            $this->totalConIva,
        );
    }

    /**
     * @param LineaPrecio[] $lineas
     */
    private static function construirSnapshot(
        string $session,
        SeleccionReserva $seleccion,
        int $diasReserva,
        array $lineas,
        float $subtotal,
        float $ivaTotal,
        float $total,
    ): string {
        $payload = [
            'moneda' => 'EUR',
            'diasReserva' => $diasReserva,
            'seleccion' => [
                'session' => $session,
                'tipoReserva' => $seleccion->tipoReserva,
                'limpieza' => $seleccion->limpiezaCodigo,
                'seguroCancelacion' => $seleccion->seguroCancelacion ? 1 : 0,
                'fechaEntrada' => $seleccion->fechaEntrada->format(
                    'Y-m-d H:i:s',
                ),
                'fechaSalida' => $seleccion->fechaSalida->format('Y-m-d H:i:s'),
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
                $lineas,
            ),
            'totales' => [
                'subtotal_sin_iva' => $subtotal,
                'iva_total' => $ivaTotal,
                'total_con_iva' => $total,
            ],
        ];

        return (string) json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
    }
}
