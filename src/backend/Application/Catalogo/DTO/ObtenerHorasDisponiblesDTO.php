<?php

declare(strict_types=1);

namespace App\Application\Catalogo\DTO;

final class ObtenerHorasDisponiblesDTO
{
    public function __construct(
        public readonly string $tipoReserva,
        public readonly string $fecha,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            tipoReserva: $data['tipo_reserva'],
            fecha: $data['fecha'],
        );
    }
}
