<?php

declare(strict_types=1);

use App\Domain\Usuario\ValueObjects\NombreCompleto;
use PHPUnit\Framework\TestCase;

class NombreCompletoTest extends TestCase
{
    public function test_nombre_valido(): void
    {
        $nombre = NombreCompleto::fromString('Joan Miró');
        $this->assertSame('Joan Miró', $nombre->value());
    }

    public function test_nombre_se_normaliza_quitando_espacios(): void
    {
        $nombre = NombreCompleto::fromString('  Joan Miró  ');
        $this->assertSame('Joan Miró', $nombre->value());
    }

    public function test_nombre_vacio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NombreCompleto::fromString('');
    }

    public function test_nombre_demasiado_largo_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        NombreCompleto::fromString(str_repeat('a', 256));
    }

    public function test_dos_nombres_iguales(): void
    {
        $a = NombreCompleto::fromString('Joan Miró');
        $b = NombreCompleto::fromString('Joan Miró');
        $this->assertTrue($a->equals($b));
    }
}
