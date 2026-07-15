<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Carrito\UseCase;

use App\Application\Carrito\UseCase\ObtenerCarritoUseCase;
use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use PHPUnit\Framework\TestCase;

final class ObtenerCarritoUseCaseTest extends TestCase
{
    private function makeCarrito(string $session = 'sess-123'): Carrito
    {
        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-01 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-05 10:00:00'),
        );

        $lineas = [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                100.0,
                21.0,
                121.0,
            ),
        ];

        return Carrito::crear($session, $seleccion, 4, $lineas);
    }

    public function test_execute_devuelve_carrito_cuando_existe(): void
    {
        $carrito = $this->makeCarrito('sess-123');

        $repository = $this->createStub(CarritoRepositoryInterface::class);
        $repository->method('findBySession')->willReturn($carrito);

        $useCase = new ObtenerCarritoUseCase($repository);
        $resultado = $useCase->execute('sess-123');

        $this->assertSame($carrito, $resultado);
    }

    public function test_execute_lanza_excepcion_si_session_vacia(): void
    {
        $repository = $this->createStub(CarritoRepositoryInterface::class);
        $useCase = new ObtenerCarritoUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_SESSION');

        $useCase->execute('');
    }

    public function test_execute_lanza_excepcion_si_session_es_solo_espacios(): void
    {
        $repository = $this->createStub(CarritoRepositoryInterface::class);
        $useCase = new ObtenerCarritoUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_SESSION');

        $useCase->execute('   ');
    }

    public function test_execute_lanza_excepcion_si_carrito_no_existe(): void
    {
        $repository = $this->createStub(CarritoRepositoryInterface::class);
        $repository->method('findBySession')->willReturn(null);

        $useCase = new ObtenerCarritoUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NOT_FOUND');

        $useCase->execute('sesion-inexistente');
    }
}
