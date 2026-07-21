<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\MarcarVehiculoDentroUseCase;
use App\Application\Reserva\UseCase\MarcarVehiculoSalidoUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\InvalidTransitionException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Shared\UsuarioUuid;
use PHPUnit\Framework\TestCase;

final class MarcarVehiculoTransicionesUseCaseTest extends TestCase
{
    private function makeReserva(EstadoVehiculo $estadoVehiculo): Reserva
    {
        return Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: $estadoVehiculo,
            fechaReserva: new \DateTimeImmutable('2026-08-07 09:00:00'),
            entradaPrevista: new \DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new \DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: null,
            matricula: null,
            personas: null,
            tipo: TipoReserva::FinguerClass,
            vuelo: null,
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function test_marcar_dentro_actualiza_correctamente(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::PendienteEntrada);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo
            ->expects($this->once())
            ->method('actualizarEstadoVehiculo')
            ->with(1, EstadoVehiculo::PendienteEntrada, EstadoVehiculo::Dentro);

        $useCase = new MarcarVehiculoDentroUseCase($repo);
        $useCase->execute(1);
    }

    public function test_marcar_dentro_lanza_excepcion_si_reserva_no_existe(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $useCase = new MarcarVehiculoDentroUseCase($repo);

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute(999);
    }

    public function test_marcar_dentro_propaga_excepcion_de_dominio_si_transicion_invalida(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::Salido);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $useCase = new MarcarVehiculoDentroUseCase($repo);

        $this->expectException(InvalidTransitionException::class);

        $useCase->execute(1);
    }

    public function test_marcar_dentro_es_idempotente_no_llama_al_repositorio(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::Dentro);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->never())->method('actualizarEstadoVehiculo');

        $useCase = new MarcarVehiculoDentroUseCase($repo);
        $useCase->execute(1);
    }

    public function test_marcar_salido_actualiza_correctamente(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::Dentro);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo
            ->expects($this->once())
            ->method('actualizarEstadoVehiculo')
            ->with(1, EstadoVehiculo::Dentro, EstadoVehiculo::Salido);

        $useCase = new MarcarVehiculoSalidoUseCase($repo);
        $useCase->execute(1);
    }

    public function test_marcar_salido_lanza_excepcion_si_reserva_no_existe(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $useCase = new MarcarVehiculoSalidoUseCase($repo);

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute(999);
    }

    public function test_marcar_salido_propaga_excepcion_de_dominio_si_transicion_invalida(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::PendienteEntrada);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $useCase = new MarcarVehiculoSalidoUseCase($repo);

        $this->expectException(InvalidTransitionException::class);

        $useCase->execute(1);
    }

    public function test_marcar_salido_es_idempotente_no_llama_al_repositorio(): void
    {
        $reserva = $this->makeReserva(EstadoVehiculo::Salido);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->never())->method('actualizarEstadoVehiculo');

        $useCase = new MarcarVehiculoSalidoUseCase($repo);
        $useCase->execute(1);
    }
}
