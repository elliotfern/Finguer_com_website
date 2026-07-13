<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Carrito\ValueObjects;

use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use PHPUnit\Framework\TestCase;

final class SeleccionReservaTest extends TestCase
{
    public function test_tiene_limpieza_devuelve_false_cuando_codigo_es_cero(): void
    {
        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-01 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-05 10:00:00'),
        );

        $this->assertFalse($seleccion->tieneLimpieza());
    }

    public function test_tiene_limpieza_devuelve_true_cuando_hay_codigo(): void
    {
        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: 'LIMPIEZA_EXT',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-01 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-05 10:00:00'),
        );

        $this->assertTrue($seleccion->tieneLimpieza());
    }

    public function test_almacena_datos_correctamente(): void
    {
        $entrada = new \DateTimeImmutable('2026-08-01 10:00:00');
        $salida = new \DateTimeImmutable('2026-08-05 10:00:00');

        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER_GOLD',
            limpiezaCodigo: '0',
            seguroCancelacion: true,
            fechaEntrada: $entrada,
            fechaSalida: $salida,
        );

        $this->assertSame('RESERVA_FINGUER_GOLD', $seleccion->tipoReserva);
        $this->assertTrue($seleccion->seguroCancelacion);
        $this->assertSame($entrada, $seleccion->fechaEntrada);
        $this->assertSame($salida, $seleccion->fechaSalida);
    }
}
