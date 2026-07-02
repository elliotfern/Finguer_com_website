<?php

declare(strict_types=1);

use App\Domain\Catalogo\ValueObjects\CodigoServicio;
use PHPUnit\Framework\TestCase;

class CodigoServicioTest extends TestCase
{
    public function test_codigo_valido(): void
    {
        $codigo = CodigoServicio::fromString('RESERVA_FINGUER');
        $this->assertSame('RESERVA_FINGUER', $codigo->value());
    }

    public function test_codigo_se_normaliza_a_mayusculas(): void
    {
        $codigo = CodigoServicio::fromString('reserva_finguer');
        $this->assertSame('RESERVA_FINGUER', $codigo->value());
    }

    public function test_codigo_vacio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CodigoServicio::fromString('');
    }

    public function test_codigo_demasiado_largo_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CodigoServicio::fromString(str_repeat('A', 51));
    }

    public function test_codigo_con_caracteres_invalidos_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        CodigoServicio::fromString('RESERVA-FINGUER');
    }

    public function test_dos_codigos_iguales(): void
    {
        $a = CodigoServicio::fromString('RESERVA_FINGUER');
        $b = CodigoServicio::fromString('reserva_finguer');
        $this->assertTrue($a->equals($b));
    }
}
