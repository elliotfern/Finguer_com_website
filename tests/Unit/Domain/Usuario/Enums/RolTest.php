<?php
// RolTest.php
declare(strict_types=1);

use App\Domain\Usuario\Enums\Rol;
use PHPUnit\Framework\TestCase;

class RolTest extends TestCase
{
    public function test_desde_string_valido(): void
    {
        $rol = Rol::from('admin');
        $this->assertSame(Rol::Admin, $rol);
    }

    public function test_cliente_anual(): void
    {
        $rol = Rol::from('cliente_anual');
        $this->assertSame(Rol::ClienteAnual, $rol);
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\ValueError::class);
        Rol::from('invalido');
    }
}
