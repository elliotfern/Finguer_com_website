<?php

declare(strict_types=1);

use App\Domain\Usuario\ValueObjects\Matricula;
use PHPUnit\Framework\TestCase;

class MatriculaTest extends TestCase
{
    public function test_matricula_espanola_valida(): void
    {
        $matricula = Matricula::fromString('1234ABC');
        $this->assertSame('1234ABC', $matricula->value());
    }

    public function test_matricula_se_normaliza_a_mayusculas(): void
    {
        $matricula = Matricula::fromString('1234abc');
        $this->assertSame('1234ABC', $matricula->value());
    }

    public function test_matricula_europea_valida(): void
    {
        $matricula = Matricula::fromString('AB-123-CD');
        $this->assertSame('AB-123-CD', $matricula->value());
    }

    public function test_matricula_vacia_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Matricula::fromString('');
    }

    public function test_matricula_demasiado_larga_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Matricula::fromString(str_repeat('A', 21));
    }

    public function test_matricula_con_caracteres_invalidos_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Matricula::fromString('1234@#$%');
    }

    public function test_dos_matriculas_iguales(): void
    {
        $a = Matricula::fromString('1234ABC');
        $b = Matricula::fromString('1234abc');
        $this->assertTrue($a->equals($b));
    }
}
