<?php

declare(strict_types=1);

use App\Domain\Usuario\Enums\AbonoEstado;
use PHPUnit\Framework\TestCase;

class AbonoEstadoTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $estado = AbonoEstado::from('activo');
        $this->assertSame(AbonoEstado::Activo, $estado);
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        AbonoEstado::from('invalido');
    }

    public function test_try_from_invalido_devuelve_null(): void
    {
        $this->assertNull(AbonoEstado::tryFrom('invalido'));
    }

    public function test_todos_los_estados(): void
    {
        $this->assertSame('activo', AbonoEstado::Activo->value);
        $this->assertSame('caducado', AbonoEstado::Caducado->value);
        $this->assertSame('cancelado', AbonoEstado::Cancelado->value);
        $this->assertSame('suspendido', AbonoEstado::Suspendido->value);
    }
}
