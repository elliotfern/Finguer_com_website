<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\ActualizarDatosReservaUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\VerificadorFacturaInterface;
use App\Domain\Shared\UsuarioUuid;
use PHPUnit\Framework\TestCase;

final class ActualizarDatosReservaUseCaseTest extends TestCase
{
    private function makeReserva(
        EstadoReserva $estado = EstadoReserva::Pendiente,
    ): Reserva {
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

    private function baseInput(): array
    {
        return [
            'id' => 1,
            'estado' => 'pendiente',
            'tipo' => '1',
            'canal' => '1',
            'entrada_prevista' => '2026-09-01 12:00:00',
            'salida_prevista' => '2026-09-05 12:00:00',
            'vehiculo' => 'BMW Serie 3',
            'matricula' => '9999XYZ',
            'personas' => 3,
            'vuelo' => 'IB5678',
            'notas' => 'Cambio de datos',
        ];
    }

    private function verificadorSinFactura(): VerificadorFacturaInterface
    {
        $v = $this->createStub(VerificadorFacturaInterface::class);
        $v->method('existeFacturaParaReserva')->willReturn(false);
        return $v;
    }

    public function test_actualiza_datos_sin_cambiar_estado(): void
    {
        $reserva = $this->makeReserva();

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->once())->method('actualizarDatosGenerales');

        $useCase = new ActualizarDatosReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );
        $resultado = $useCase->execute($this->baseInput());

        $this->assertSame('BMW Serie 3', $resultado->vehiculo());
        $this->assertSame(EstadoReserva::Pendiente, $resultado->estado());
    }

    public function test_permite_forzar_cambio_de_estado_sin_factura(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $useCase = new ActualizarDatosReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );

        $input = $this->baseInput();
        $input['estado'] = 'pagada';

        $resultado = $useCase->execute($input);

        $this->assertSame(EstadoReserva::Pagada, $resultado->estado());
    }

    public function test_lanza_excepcion_si_reserva_no_existe(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn(null);

        $useCase = new ActualizarDatosReservaUseCase(
            $repo,
            $this->verificadorSinFactura(),
        );

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute($this->baseInput());
    }

    public function test_falla_si_cambia_estado_y_tiene_factura(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);

        $verificador = $this->createStub(VerificadorFacturaInterface::class);
        $verificador->method('existeFacturaParaReserva')->willReturn(true);

        $useCase = new ActualizarDatosReservaUseCase($repo, $verificador);

        $input = $this->baseInput();
        $input['estado'] = 'cancelada';

        $this->expectException(ReservaConFacturaException::class);

        $useCase->execute($input);
    }

    public function test_permite_actualizar_otros_datos_con_factura_si_estado_no_cambia(): void
    {
        $reserva = $this->makeReserva(EstadoReserva::Pendiente);

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findById')->willReturn($reserva);
        $repo->expects($this->once())->method('actualizarDatosGenerales');

        $verificador = $this->createStub(VerificadorFacturaInterface::class);
        $verificador->method('existeFacturaParaReserva')->willReturn(true);

        $useCase = new ActualizarDatosReservaUseCase($repo, $verificador);

        $input = $this->baseInput();
        $input['estado'] = 'pendiente'; // mismo estado, no cambia

        $resultado = $useCase->execute($input);

        $this->assertSame('BMW Serie 3', $resultado->vehiculo());
    }
}
