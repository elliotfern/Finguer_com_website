<?php
// UsuarioEstadoTest.php
declare(strict_types=1);

use App\Domain\Usuario\Enums\UsuarioEstado;
use PHPUnit\Framework\TestCase;

class UsuarioEstadoTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $estado = UsuarioEstado::from('activo');
        $this->assertSame(UsuarioEstado::Activo, $estado);
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        UsuarioEstado::from('invalido');
    }

    public function test_try_from_invalido_devuelve_null(): void
    {
        $this->assertNull(UsuarioEstado::tryFrom('invalido'));
    }
}
