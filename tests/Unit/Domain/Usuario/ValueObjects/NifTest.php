<?php

declare(strict_types=1);

use App\Domain\Usuario\ValueObjects\Nif;
use PHPUnit\Framework\TestCase;

class NifTest extends TestCase
{
    public function test_nif_espanol_valido(): void
    {
        $nif = Nif::fromString('12345678Z');
        $this->assertSame('12345678Z', $nif->value());
    }

    public function test_nif_aleman_valido(): void
    {
        $nif = Nif::fromString('DE123456789');
        $this->assertSame('DE123456789', $nif->value());
    }

    public function test_nif_se_normaliza_a_mayusculas(): void
    {
        $nif = Nif::fromString('12345678z');
        $this->assertSame('12345678Z', $nif->value());
    }

    public function test_nif_vacio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Nif::fromString('');
    }

    public function test_nif_demasiado_largo_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Nif::fromString(str_repeat('A', 31));
    }

    public function test_nif_con_caracteres_invalidos_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Nif::fromString('12345@#$%');
    }

    public function test_dos_nif_iguales(): void
    {
        $a = Nif::fromString('12345678Z');
        $b = Nif::fromString('12345678z');
        $this->assertTrue($a->equals($b));
    }
}
