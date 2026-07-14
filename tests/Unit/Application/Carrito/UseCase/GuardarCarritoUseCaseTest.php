<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Carrito\UseCase;

use App\Application\Carrito\DTO\GuardarCarritoDTO;
use App\Application\Carrito\Exception\ReglaNegocioException;
use App\Application\Carrito\UseCase\GuardarCarritoUseCase;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class GuardarCarritoUseCaseTest extends TestCase
{
    private function makeTarifa(string $codigo = 'RESERVA_FINGUER'): Servicio
    {
        return Servicio::fromDatabase([
            'id' => 1,
            'codigo' => $codigo,
            'nombre' => 'Reserva Finguer Class',
            'tipo' => 'parking',
            'modo_precio' => 'FIJO',
            'iva_percent' => 21.0,
            'activo' => 1,
            'precio_base' => null,
            'dias_incluidos' => 10,
            'min_con_iva' => 100.0,
            'extra_dia_con_iva' => 5.0,
            'seg_umbral_con_iva' => null,
            'seg_min_con_iva' => null,
            'seg_factor' => null,
        ]);
    }

    private function fechaValidaEntrada(): string
    {
        // Antelación mínima 2 días + hora válida (07:00 es hora válida Finguer Class)
        return new \DateTimeImmutable('+5 days 07:00:00')->format(
            'Y-m-d H:i:s',
        );
    }

    private function fechaValidaSalida(): string
    {
        return new \DateTimeImmutable('+8 days 07:00:00')->format(
            'Y-m-d H:i:s',
        );
    }

    public function test_guarda_carrito_correctamente_con_datos_validos(): void
    {
        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'RESERVA_FINGUER',
            'limpieza' => '0',
            'seguroCancelacion' => 0,
            'fechaEntrada' => $this->fechaValidaEntrada(),
            'fechaSalida' => $this->fechaValidaSalida(),
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn($this->makeTarifa());

        $carritoRepo = $this->createMock(CarritoRepositoryInterface::class);
        $carritoRepo->expects($this->once())->method('save');

        $useCase = new GuardarCarritoUseCase($carritoRepo, $servicioRepo);
        $carrito = $useCase->execute($dto);

        $this->assertSame('sess-123', $carrito->session());
        $this->assertSame(3, $carrito->diasReserva());
    }

    public function test_lanza_excepcion_si_falta_session(): void
    {
        $dto = GuardarCarritoDTO::fromArray([
            'session' => '',
            'tipoReserva' => 'RESERVA_FINGUER',
            'fechaEntrada' => $this->fechaValidaEntrada(),
            'fechaSalida' => $this->fechaValidaSalida(),
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_SESSION');

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }

    public function test_lanza_excepcion_si_faltan_fechas(): void
    {
        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'RESERVA_FINGUER',
            'fechaEntrada' => '',
            'fechaSalida' => '',
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_FECHAS');

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }

    public function test_lanza_regla_negocio_si_rango_de_fechas_invalido(): void
    {
        // Salida antes que entrada
        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'RESERVA_FINGUER',
            'fechaEntrada' => $this->fechaValidaSalida(),
            'fechaSalida' => $this->fechaValidaEntrada(),
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(ReglaNegocioException::class);

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }

    public function test_lanza_regla_negocio_si_hora_no_disponible(): void
    {
        // 06:00 no está en el horario de Finguer Class (empieza a las 07:00)
        $entrada = new \DateTimeImmutable('+5 days 06:00:00')->format(
            'Y-m-d H:i:s',
        );
        $salida = new \DateTimeImmutable('+8 days 07:00:00')->format(
            'Y-m-d H:i:s',
        );

        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'RESERVA_FINGUER',
            'fechaEntrada' => $entrada,
            'fechaSalida' => $salida,
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(ReglaNegocioException::class);

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }

    public function test_lanza_excepcion_si_tipo_reserva_no_existe_en_catalogo(): void
    {
        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'TIPO_INEXISTENTE',
            'fechaEntrada' => $this->fechaValidaEntrada(),
            'fechaSalida' => $this->fechaValidaSalida(),
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn(null);

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(\InvalidArgumentException::class);

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }

    public function test_lanza_excepcion_si_limpieza_no_existe_en_catalogo(): void
    {
        $dto = GuardarCarritoDTO::fromArray([
            'session' => 'sess-123',
            'tipoReserva' => 'RESERVA_FINGUER',
            'limpieza' => 'LIMPIEZA_INEXISTENTE',
            'fechaEntrada' => $this->fechaValidaEntrada(),
            'fechaSalida' => $this->fechaValidaSalida(),
        ]);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo
            ->method('findByCodigo')
            ->willReturnCallback(
                fn($codigo) => $codigo->value() === 'RESERVA_FINGUER'
                    ? $this->makeTarifa()
                    : null,
            );

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'LIMPIEZA_NO_VALIDA: LIMPIEZA_INEXISTENTE',
        );

        new GuardarCarritoUseCase($carritoRepo, $servicioRepo)->execute($dto);
    }
}
