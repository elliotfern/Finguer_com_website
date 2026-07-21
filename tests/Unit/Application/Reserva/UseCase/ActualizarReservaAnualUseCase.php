<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\ActualizarReservaAnualUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Shared\UsuarioUuid;
use PHPUnit\Framework\TestCase;

final class ActualizarReservaAnualUseCaseTest extends TestCase
{
    private function makeReservaAnual(): Reserva
    {
        return Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new \DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            vuelo: null,
            notas: null,
        )->conId(1);
    }

    private function baseInput(): array
    {
        return [
            'localizador' => '0708269999',
            'diaEntrada' => '2026-09-01',
            'horaEntrada' => '12:00',
            'diaSalida' => null,
            'horaSalida' => null,
            'vehiculo' => 'BMW Serie 3',
            'matricula' => '9999XYZ',
            'vuelo' => 'IB5678',
            'notes' => 'Nota actualizada',
        ];
    }

    public function test_actualiza_correctamente(): void
    {
        $reserva = $this->makeReservaAnual();

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findByLocalizador')->willReturn($reserva);
        $repo->expects($this->once())->method('actualizarDatosAnual');

        $useCase = new ActualizarReservaAnualUseCase($repo);
        $resultado = $useCase->execute($this->baseInput());

        $this->assertSame('BMW Serie 3', $resultado->vehiculo());
        $this->assertSame('9999XYZ', $resultado->matricula());
    }

    public function test_lanza_excepcion_si_no_existe(): void
    {
        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findByLocalizador')->willReturn(null);

        $useCase = new ActualizarReservaAnualUseCase($repo);

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute($this->baseInput());
    }

    public function test_lanza_excepcion_si_entrada_invalida(): void
    {
        $reserva = $this->makeReservaAnual();

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findByLocalizador')->willReturn($reserva);

        $useCase = new ActualizarReservaAnualUseCase($repo);

        $input = $this->baseInput();
        $input['diaEntrada'] = 'fecha-invalida';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ENTRADA_INVALIDA');

        $useCase->execute($input);
    }

    public function test_lanza_excepcion_si_salida_anterior_a_entrada(): void
    {
        $reserva = $this->makeReservaAnual();

        $repo = $this->createStub(ReservaRepositoryInterface::class);
        $repo->method('findByLocalizador')->willReturn($reserva);

        $useCase = new ActualizarReservaAnualUseCase($repo);

        $input = $this->baseInput();
        $input['diaSalida'] = '2026-08-01';
        $input['horaSalida'] = '10:00';

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('SALIDA_ANTERIOR_A_ENTRADA');

        $useCase->execute($input);
    }

    public function test_persiste_los_cambios_a_traves_del_repositorio(): void
    {
        $reserva = $this->makeReservaAnual();

        $repo = $this->createMock(ReservaRepositoryInterface::class);
        $repo->method('findByLocalizador')->willReturn($reserva);
        $repo
            ->expects($this->once())
            ->method('actualizarDatosAnual')
            ->with(
                $this->callback(
                    fn(Reserva $r) => $r->vehiculo() === 'BMW Serie 3' &&
                        $r->matricula() === '9999XYZ',
                ),
            );

        $useCase = new ActualizarReservaAnualUseCase($repo);
        $useCase->execute($this->baseInput());
    }
}
