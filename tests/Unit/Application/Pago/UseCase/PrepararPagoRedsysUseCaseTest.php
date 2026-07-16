<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Pago\UseCase;

use App\Application\Pago\UseCase\PrepararPagoRedsysUseCase;
use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use App\Domain\Pago\Service\PasarelaPagoInterface;
use App\Domain\Pago\ValueObjects\PagoRedsysParams;
use App\Domain\Reserva\Service\LocalizadorGeneratorInterface;
use PHPUnit\Framework\TestCase;

final class PrepararPagoRedsysUseCaseTest extends TestCase
{
    private function makeCarrito(float $totalConIva = 121.0): Carrito
    {
        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-07 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-11 10:00:00'),
        );

        $lineas = [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                round($totalConIva / 1.21, 2),
                round($totalConIva - $totalConIva / 1.21, 2),
                $totalConIva,
            ),
        ];

        return Carrito::crear('sess-123', $seleccion, 4, $lineas);
    }

    public function test_prepara_pago_correctamente_con_carrito_valido(): void
    {
        $carrito = $this->makeCarrito(121.0);

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $localizadorGen->method('generar')->willReturn('0708261234');

        $pasarelaPago = $this->createMock(PasarelaPagoInterface::class);
        $pasarelaPago
            ->expects($this->once())
            ->method('prepararPago')
            ->with('0708261234', 121.0)
            ->willReturn(
                new PagoRedsysParams('params-base64', 'signature-base64'),
            );

        $useCase = new PrepararPagoRedsysUseCase(
            $carritoRepo,
            $localizadorGen,
            $pasarelaPago,
        );
        $result = $useCase->execute('sess-123');

        $this->assertSame('0708261234', $result->localizador);
        $this->assertSame('params-base64', $result->params->params);
        $this->assertSame('signature-base64', $result->params->signature);
    }

    public function test_lanza_excepcion_si_session_vacia(): void
    {
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $pasarelaPago = $this->createStub(PasarelaPagoInterface::class);

        $useCase = new PrepararPagoRedsysUseCase(
            $carritoRepo,
            $localizadorGen,
            $pasarelaPago,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_SESSION');

        $useCase->execute('');
    }

    public function test_lanza_excepcion_si_carrito_no_existe(): void
    {
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn(null);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $pasarelaPago = $this->createStub(PasarelaPagoInterface::class);

        $useCase = new PrepararPagoRedsysUseCase(
            $carritoRepo,
            $localizadorGen,
            $pasarelaPago,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CARRITO_NOT_FOUND');

        $useCase->execute('sesion-inexistente');
    }

    public function test_lanza_excepcion_si_importe_es_cero(): void
    {
        $carrito = $this->makeCarrito(0.0);

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );
        $pasarelaPago = $this->createStub(PasarelaPagoInterface::class);

        $useCase = new PrepararPagoRedsysUseCase(
            $carritoRepo,
            $localizadorGen,
            $pasarelaPago,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IMPORTE_INVALIDO');

        $useCase->execute('sess-123');
    }

    public function test_no_llama_a_la_pasarela_si_el_carrito_no_existe(): void
    {
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn(null);

        $localizadorGen = $this->createStub(
            LocalizadorGeneratorInterface::class,
        );

        $pasarelaPago = $this->createMock(PasarelaPagoInterface::class);
        $pasarelaPago->expects($this->never())->method('prepararPago');

        $useCase = new PrepararPagoRedsysUseCase(
            $carritoRepo,
            $localizadorGen,
            $pasarelaPago,
        );

        try {
            $useCase->execute('sesion-inexistente');
        } catch (\InvalidArgumentException) {
            // esperado
        }
    }
}
