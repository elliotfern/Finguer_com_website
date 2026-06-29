<?php

declare(strict_types=1);

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\ValueObjects\Matricula;
use PHPUnit\Framework\TestCase;

class AbonoTest extends TestCase
{
    private UsuarioUuid $id;
    private UsuarioUuid $usuarioUuid;
    private Matricula $matricula;

    protected function setUp(): void
    {
        $this->id = UsuarioUuid::generate();
        $this->usuarioUuid = UsuarioUuid::generate();
        $this->matricula = Matricula::fromString('1234ABC');
    }

    public function test_crear_abono_valido(): void
    {
        $abono = Abono::create(
            $this->id,
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            $this->matricula,
        );

        $this->assertSame(AbonoEstado::Activo, $abono->estado());
        $this->assertSame(10, $abono->limiteReservas());
        $this->assertTrue($abono->estaActivo());
    }

    public function test_fecha_fin_anterior_a_inicio_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Abono::create(
            $this->id,
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-12-31'),
            new \DateTimeImmutable('2026-01-01'),
            $this->matricula,
        );
    }

    public function test_limite_reservas_cero_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Abono::create(
            $this->id,
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            $this->matricula,
            0,
        );
    }

    public function test_abono_caducado_no_esta_activo(): void
    {
        $abono = Abono::fromDatabase(
            $this->id,
            $this->usuarioUuid,
            AbonoEstado::Caducado,
            new \DateTimeImmutable('2025-01-01'),
            new \DateTimeImmutable('2025-12-31'),
            10,
            $this->matricula,
            null,
            null,
        );

        $this->assertFalse($abono->estaActivo());
    }

    public function test_abono_vigente_en_fecha(): void
    {
        $abono = Abono::create(
            $this->id,
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            $this->matricula,
        );

        $this->assertTrue(
            $abono->estaVigente(new \DateTimeImmutable('2026-06-15')),
        );
        $this->assertFalse(
            $abono->estaVigente(new \DateTimeImmutable('2025-06-15')),
        );
    }
}
