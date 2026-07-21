<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Entity;

use App\Domain\Catalogo\Rules\ReglasReserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\InvalidTransitionException;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use App\Domain\Shared\UsuarioUuid;
use DateTimeImmutable;

final class Reserva
{
    /**
     * @param ReservaServicioLinea[] $lineas
     */
    private function __construct(
        private readonly ?int $id,
        private readonly UsuarioUuid $usuarioUuid,
        private readonly string $localizador,
        private readonly EstadoReserva $estado,
        private readonly EstadoVehiculo $estadoVehiculo,
        private readonly DateTimeImmutable $fechaReserva,
        private readonly DateTimeImmutable $entradaPrevista,
        private readonly DateTimeImmutable $salidaPrevista,
        private readonly ?float $subtotalCalculado,
        private readonly ?float $ivaCalculado,
        private readonly ?float $totalCalculado,
        private readonly ?string $vehiculo,
        private readonly ?string $matricula,
        private readonly ?int $personas,
        private readonly TipoReserva $tipo,
        private readonly ?string $vuelo,
        private readonly ?string $notas,
        private readonly CanalReserva $canal,
        private readonly array $lineas,
        private readonly ?DateTimeImmutable $createdAt = null,
        private readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    /**
     * @param ReservaServicioLinea[] $lineas
     */
    public static function crear(
        UsuarioUuid $usuarioUuid,
        string $localizador,
        DateTimeImmutable $entradaPrevista,
        DateTimeImmutable $salidaPrevista,
        float $subtotalCalculado,
        float $ivaCalculado,
        float $totalCalculado,
        ?string $vehiculo,
        ?string $matricula,
        ?int $personas,
        TipoReserva $tipo,
        ?string $vuelo,
        array $lineas,
        ?string $notas = null,
    ): self {
        $now = new DateTimeImmutable(
            'now',
            new \DateTimeZone(ReglasReserva::TIMEZONE),
        );

        return new self(
            id: null,
            usuarioUuid: $usuarioUuid,
            localizador: $localizador,
            estado: EstadoReserva::Pendiente,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: $now,
            entradaPrevista: $entradaPrevista,
            salidaPrevista: $salidaPrevista,
            subtotalCalculado: $subtotalCalculado,
            ivaCalculado: $ivaCalculado,
            totalCalculado: $totalCalculado,
            vehiculo: $vehiculo,
            matricula: $matricula,
            personas: $personas,
            tipo: $tipo,
            vuelo: $vuelo,
            notas: $notas,
            canal: CanalReserva::Web,
            lineas: $lineas,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    /**
     * @param ReservaServicioLinea[];
     */
    public static function crearAnual(
        UsuarioUuid $usuarioUuid,
        string $localizador,
        DateTimeImmutable $entradaPrevista,
        ?DateTimeImmutable $salidaPrevista,
        ?string $vehiculo,
        ?string $matricula,
        ?string $vuelo,
        ?string $notas,
    ): self {
        $now = new DateTimeImmutable(
            'now',
            new \DateTimeZone(ReglasReserva::TIMEZONE),
        );

        return new self(
            id: null,
            usuarioUuid: $usuarioUuid,
            localizador: $localizador,
            estado: EstadoReserva::Anual,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: $now,
            entradaPrevista: $entradaPrevista,
            salidaPrevista: $salidaPrevista ??
                new DateTimeImmutable('2030-01-01 00:00:00'),
            subtotalCalculado: null,
            ivaCalculado: null,
            totalCalculado: null,
            vehiculo: $vehiculo,
            matricula: $matricula,
            personas: null,
            tipo: TipoReserva::Anual,
            vuelo: $vuelo,
            notas: $notas,
            canal: CanalReserva::Anual,
            lineas: [],
            createdAt: $now,
            updatedAt: $now,
        );
    }

    // En Reserva.php

    public function actualizarDatosAnual(
        DateTimeImmutable $entradaPrevista,
        ?DateTimeImmutable $salidaPrevista,
        ?string $vehiculo,
        ?string $matricula,
        ?string $vuelo,
        ?string $notas,
    ): self {
        if ($this->estado !== EstadoReserva::Anual) {
            throw new \DomainException(
                'Solo se pueden actualizar datos de reservas de tipo anual.',
            );
        }

        return new self(
            $this->id,
            $this->usuarioUuid,
            $this->localizador,
            $this->estado,
            $this->estadoVehiculo,
            $this->fechaReserva,
            $entradaPrevista,
            $salidaPrevista ?? $this->salidaPrevista,
            $this->subtotalCalculado,
            $this->ivaCalculado,
            $this->totalCalculado,
            $vehiculo,
            $matricula,
            $this->personas,
            $this->tipo,
            $vuelo,
            $notas,
            $this->canal,
            $this->lineas,
            $this->createdAt,
            new DateTimeImmutable(
                'now',
                new \DateTimeZone(ReglasReserva::TIMEZONE),
            ),
        );
    }

    /**
     * @param ReservaServicioLinea[] $lineas
     */
    public static function fromDatabase(
        int $id,
        UsuarioUuid $usuarioUuid,
        string $localizador,
        EstadoReserva $estado,
        EstadoVehiculo $estadoVehiculo,
        DateTimeImmutable $fechaReserva,
        DateTimeImmutable $entradaPrevista,
        DateTimeImmutable $salidaPrevista,
        float $subtotalCalculado,
        float $ivaCalculado,
        float $totalCalculado,
        ?string $vehiculo,
        ?string $matricula,
        ?int $personas,
        TipoReserva $tipo,
        ?string $vuelo,
        ?string $notas,
        CanalReserva $canal,
        array $lineas,
        ?DateTimeImmutable $createdAt,
        ?DateTimeImmutable $updatedAt,
    ): self {
        return new self(
            $id,
            $usuarioUuid,
            $localizador,
            $estado,
            $estadoVehiculo,
            $fechaReserva,
            $entradaPrevista,
            $salidaPrevista,
            $subtotalCalculado,
            $ivaCalculado,
            $totalCalculado,
            $vehiculo,
            $matricula,
            $personas,
            $tipo,
            $vuelo,
            $notas,
            $canal,
            $lineas,
            $createdAt,
            $updatedAt,
        );
    }

    public function id(): ?int
    {
        return $this->id;
    }
    public function usuarioUuid(): UsuarioUuid
    {
        return $this->usuarioUuid;
    }
    public function localizador(): string
    {
        return $this->localizador;
    }
    public function estado(): EstadoReserva
    {
        return $this->estado;
    }
    public function estadoVehiculo(): EstadoVehiculo
    {
        return $this->estadoVehiculo;
    }
    public function fechaReserva(): DateTimeImmutable
    {
        return $this->fechaReserva;
    }
    public function entradaPrevista(): DateTimeImmutable
    {
        return $this->entradaPrevista;
    }
    public function salidaPrevista(): DateTimeImmutable
    {
        return $this->salidaPrevista;
    }

    public function subtotalCalculado(): ?float
    {
        return $this->subtotalCalculado;
    }
    public function ivaCalculado(): ?float
    {
        return $this->ivaCalculado;
    }
    public function totalCalculado(): ?float
    {
        return $this->totalCalculado;
    }

    public function vehiculo(): ?string
    {
        return $this->vehiculo;
    }
    public function matricula(): ?string
    {
        return $this->matricula;
    }
    public function personas(): ?int
    {
        return $this->personas;
    }
    public function tipo(): TipoReserva
    {
        return $this->tipo;
    }
    public function vuelo(): ?string
    {
        return $this->vuelo;
    }
    public function notas(): ?string
    {
        return $this->notas;
    }
    public function canal(): CanalReserva
    {
        return $this->canal;
    }

    /** @return ReservaServicioLinea[] */
    public function lineas(): array
    {
        return $this->lineas;
    }
    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }
    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    /**
     * Devuelve una nueva instancia con el id asignado (tras el INSERT en BD).
     * El resto de campos se preserva igual.
     */
    public function conId(int $id): self
    {
        return new self(
            $id,
            $this->usuarioUuid,
            $this->localizador,
            $this->estado,
            $this->estadoVehiculo,
            $this->fechaReserva,
            $this->entradaPrevista,
            $this->salidaPrevista,
            $this->subtotalCalculado,
            $this->ivaCalculado,
            $this->totalCalculado,
            $this->vehiculo,
            $this->matricula,
            $this->personas,
            $this->tipo,
            $this->vuelo,
            $this->notas,
            $this->canal,
            $this->lineas,
            $this->createdAt,
            $this->updatedAt,
        );
    }

    public function marcarVehiculoDentro(): self
    {
        if ($this->estadoVehiculo === EstadoVehiculo::Dentro) {
            return $this; // idempotente: ya está en el estado destino
        }

        if ($this->estadoVehiculo !== EstadoVehiculo::PendienteEntrada) {
            throw new InvalidTransitionException(
                id: $this->id ?? 0,
                from: $this->estadoVehiculo->value,
                to: EstadoVehiculo::Dentro->value,
                allowedTo: $this->transicionesPermitidas(),
            );
        }

        return $this->conEstadoVehiculo(EstadoVehiculo::Dentro);
    }

    public function marcarVehiculoSalido(): self
    {
        if ($this->estadoVehiculo === EstadoVehiculo::Salido) {
            return $this; // idempotente: ya está en el estado destino
        }

        if ($this->estadoVehiculo !== EstadoVehiculo::Dentro) {
            throw new InvalidTransitionException(
                id: $this->id ?? 0,
                from: $this->estadoVehiculo->value,
                to: EstadoVehiculo::Salido->value,
                allowedTo: $this->transicionesPermitidas(),
            );
        }

        return $this->conEstadoVehiculo(EstadoVehiculo::Salido);
    }

    /**
     * @return string[]
     */
    private function transicionesPermitidas(): array
    {
        return match ($this->estadoVehiculo) {
            EstadoVehiculo::PendienteEntrada => [EstadoVehiculo::Dentro->value],
            EstadoVehiculo::Dentro => [EstadoVehiculo::Salido->value],
            EstadoVehiculo::Salido => [],
        };
    }

    private function conEstadoVehiculo(EstadoVehiculo $nuevo): self
    {
        return new self(
            $this->id,
            $this->usuarioUuid,
            $this->localizador,
            $this->estado,
            $nuevo,
            $this->fechaReserva,
            $this->entradaPrevista,
            $this->salidaPrevista,
            $this->subtotalCalculado,
            $this->ivaCalculado,
            $this->totalCalculado,
            $this->vehiculo,
            $this->matricula,
            $this->personas,
            $this->tipo,
            $this->vuelo,
            $this->notas,
            $this->canal,
            $this->lineas,
            $this->createdAt,
            new \DateTimeImmutable(
                'now',
                new \DateTimeZone(
                    \App\Domain\Catalogo\Rules\ReglasReserva::TIMEZONE,
                ),
            ),
        );
    }

    public function cancelar(): self
    {
        if ($this->estado === EstadoReserva::Cancelada) {
            return $this; // idempotente: ya está cancelada
        }

        if ($this->estado === EstadoReserva::Pagada) {
            throw new InvalidTransitionException(
                id: $this->id ?? 0,
                from: $this->estado->value,
                to: EstadoReserva::Cancelada->value,
                allowedTo: [],
            );
        }

        return $this->conEstado(EstadoReserva::Cancelada);
    }

    private function conEstado(EstadoReserva $nuevo): self
    {
        return new self(
            $this->id,
            $this->usuarioUuid,
            $this->localizador,
            $nuevo,
            $this->estadoVehiculo,
            $this->fechaReserva,
            $this->entradaPrevista,
            $this->salidaPrevista,
            $this->subtotalCalculado,
            $this->ivaCalculado,
            $this->totalCalculado,
            $this->vehiculo,
            $this->matricula,
            $this->personas,
            $this->tipo,
            $this->vuelo,
            $this->notas,
            $this->canal,
            $this->lineas,
            $this->createdAt,
            new DateTimeImmutable(
                'now',
                new \DateTimeZone(ReglasReserva::TIMEZONE),
            ),
        );
    }
}
