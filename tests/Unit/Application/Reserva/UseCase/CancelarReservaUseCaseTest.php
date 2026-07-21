<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\CancelarReservaUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\InvalidTransitionException;
use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\VerificadorFacturaInterface;
use App\Domain\Shared\UsuarioUuid;
use PHPUnit\Framework\TestCase;

final class CancelarReservaUseCaseTest extends TestCase
{
    private function makeReserva(EstadoReserva $estado): Reserva
    {
        return Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: $estado,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
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

    private function verificadorSinFactura(): VerificadorFacturaInterface
    {
        $verificador = $this->createStub(VerificadorFacturaInterface::class);
        $verificador->method('existeFacturaParaReserva')->willReturn(false);
        return $verificador;
    }

    public function test_cancela_correctamente_desde_pendiente(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo
            ->expects($this->once())
            ->method('actualizarEstado')
            ->with(1, EstadoReserva::Pendiente, EstadoReserva::Cancelada);

        $useCase = new CancelarReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );
        $useCase->execute(1);
    }

    public function test_lanza_excepcion_si_reserva_no_existe(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $useCase = new CancelarReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute(999);
    }

    public function test_falla_si_esta_pagada(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pagada);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $useCase = new CancelarReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );

        $this->expectException(InvalidTransitionException::class);

        $useCase->execute(1);
    }

    public function test_es_idempotente_no_llama_al_repositorio_si_ya_esta_cancelada(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Cancelada);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->never())->method('actualizarEstado');

        $useCase = new CancelarReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );
        $useCase->execute(1);
    }

    public function test_falla_si_tiene_factura_emitida(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $verificador = $this->createStub(VerificadorFacturaInterface::class);
        $verificador->method('existeFacturaParaReserva')->willReturn(true);

        $useCase = new CancelarReservaUseCase($repo, $verificador);

        $this->expectException(ReservaConFacturaException::class);

        $useCase->execute(1);
    }

    public function test_no_llama_al_repositorio_si_tiene_factura(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->never())->method('actualizarEstado');

        $verificador = $this->createStub(VerificadorFacturaInterface::class);
        $verificador->method('existeFacturaParaReserva')->willReturn(true);

        $useCase = new CancelarReservaUseCase($repo, $verificador);

        try {
            $useCase->execute(1);
        } catch (ReservaConFacturaException) {
            // esperado
        }
    }
}
