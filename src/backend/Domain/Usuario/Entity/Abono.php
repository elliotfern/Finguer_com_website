<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Entity;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\ValueObjects\Matricula;

final class Abono
{
    private function __construct(
        private readonly UsuarioUuid $id,
        private readonly UsuarioUuid $usuarioUuid,
        private readonly AbonoEstado $estado,
        private readonly \DateTimeImmutable $fechaInicio,
        private readonly \DateTimeImmutable $fechaFin,
        private readonly int $limiteReservas,
        private readonly Matricula $matricula,
        private readonly ?string $vehiculo,
        private readonly ?string $observaciones,
    ) {}

    public static function create(
        UsuarioUuid $id,
        UsuarioUuid $usuarioUuid,
        \DateTimeImmutable $fechaInicio,
        \DateTimeImmutable $fechaFin,
        Matricula $matricula,
        int $limiteReservas = 10,
        ?string $vehiculo = null,
        ?string $observaciones = null,
    ): self {
        if ($fechaFin <= $fechaInicio) {
            throw new \InvalidArgumentException(
                'La fecha de fin debe ser posterior a la fecha de inicio.',
            );
        }

        if ($limiteReservas <= 0) {
            throw new \InvalidArgumentException(
                'El límite de reservas debe ser mayor que 0.',
            );
        }

        return new self(
            id: $id,
            usuarioUuid: $usuarioUuid,
            estado: AbonoEstado::Activo,
            fechaInicio: $fechaInicio,
            fechaFin: $fechaFin,
            limiteReservas: $limiteReservas,
            matricula: $matricula,
            vehiculo: $vehiculo,
            observaciones: $observaciones,
        );
    }

    public static function fromDatabase(
        UsuarioUuid $id,
        UsuarioUuid $usuarioUuid,
        AbonoEstado $estado,
        \DateTimeImmutable $fechaInicio,
        \DateTimeImmutable $fechaFin,
        int $limiteReservas,
        Matricula $matricula,
        ?string $vehiculo,
        ?string $observaciones,
    ): self {
        return new self(
            $id,
            $usuarioUuid,
            $estado,
            $fechaInicio,
            $fechaFin,
            $limiteReservas,
            $matricula,
            $vehiculo,
            $observaciones,
        );
    }

    public function id(): UsuarioUuid
    {
        return $this->id;
    }
    public function usuarioUuid(): UsuarioUuid
    {
        return $this->usuarioUuid;
    }
    public function estado(): AbonoEstado
    {
        return $this->estado;
    }
    public function fechaInicio(): \DateTimeImmutable
    {
        return $this->fechaInicio;
    }
    public function fechaFin(): \DateTimeImmutable
    {
        return $this->fechaFin;
    }
    public function limiteReservas(): int
    {
        return $this->limiteReservas;
    }
    public function matricula(): Matricula
    {
        return $this->matricula;
    }
    public function vehiculo(): ?string
    {
        return $this->vehiculo;
    }
    public function observaciones(): ?string
    {
        return $this->observaciones;
    }

    public function estaActivo(): bool
    {
        return $this->estado === AbonoEstado::Activo &&
            $this->fechaFin >= new \DateTimeImmutable('today');
    }

    public function estaVigente(\DateTimeImmutable $fecha): bool
    {
        return $this->estaActivo() &&
            $fecha >= $this->fechaInicio &&
            $fecha <= $this->fechaFin;
    }
}
