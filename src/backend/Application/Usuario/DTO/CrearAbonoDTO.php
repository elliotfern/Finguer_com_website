<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

final class CrearAbonoDTO
{
    public function __construct(
        public readonly string $usuarioUuid,
        public readonly string $fechaInicio,
        public readonly string $fechaFin,
        public readonly string $matricula,
        public readonly int $limiteReservas = 10,
        public readonly ?string $vehiculo = null,
        public readonly ?string $observaciones = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            usuarioUuid: $data['usuario_uuid'],
            fechaInicio: $data['fecha_inicio'],
            fechaFin: $data['fecha_fin'],
            matricula: $data['matricula'],
            limiteReservas: (int) ($data['limite_reservas'] ?? 10),
            vehiculo: $data['vehiculo'] ?? null,
            observaciones: $data['observaciones'] ?? null,
        );
    }
}
