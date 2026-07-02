<?php

declare(strict_types=1);

use App\Domain\Catalogo\Enums\ModoPrecio;
use PHPUnit\Framework\TestCase;

class ModoPrecioTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $this->assertSame(ModoPrecio::Fijo, ModoPrecio::from('FIJO'));
        $this->assertSame(
            ModoPrecio::PorcentajeCondicional,
            ModoPrecio::from('PORCENTAJE_CONDICIONAL'),
        );
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        ModoPrecio::from('invalido');
    }
}
