<?php

declare(strict_types=1);

use App\Domain\Catalogo\Enums\TipoServicio;
use PHPUnit\Framework\TestCase;

class TipoServicioTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $this->assertSame(TipoServicio::Parking, TipoServicio::from('parking'));
        $this->assertSame(TipoServicio::Extra, TipoServicio::from('extra'));
        $this->assertSame(TipoServicio::Seguro, TipoServicio::from('seguro'));
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        TipoServicio::from('invalido');
    }
}
