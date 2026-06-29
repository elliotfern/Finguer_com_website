<?php

declare(strict_types=1);

use App\Domain\Usuario\ValueObjects\Telefono;
use PHPUnit\Framework\TestCase;

class TelefonoTest extends TestCase
{
    public function test_telefono_nacional_valido(): void
    {
        $tel = Telefono::fromString('689255821');
        $this->assertSame('689255821', $tel->value());
    }

    public function test_telefono_internacional_valido(): void
    {
        $tel = Telefono::fromString('+34 689 255 821');
        $this->assertSame('+34 689 255 821', $tel->value());
    }

    public function test_telefono_vacio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Telefono::fromString('');
    }

    public function test_telefono_invalido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Telefono::fromString('abc-no-es-telefono');
    }

    public function test_dos_telefonos_iguales(): void
    {
        $a = Telefono::fromString('+34 689 255 821');
        $b = Telefono::fromString('+34 689 255 821');
        $this->assertTrue($a->equals($b));
    }
}
