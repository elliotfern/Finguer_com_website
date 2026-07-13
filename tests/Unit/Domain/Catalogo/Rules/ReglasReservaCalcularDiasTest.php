<?php

declare(strict_types=1);

use App\Domain\Catalogo\Rules\ReglasReserva;
use PHPUnit\Framework\TestCase;

class ReglasReservaCalcularDiasTest extends TestCase
{
    private DateTimeZone $tz;

    protected function setUp(): void
    {
        $this->tz = new DateTimeZone(ReglasReserva::TIMEZONE);
    }

    public function test_un_dia_exacto(): void
    {
        $entrada = new DateTimeImmutable('2026-08-01 10:00:00', $this->tz);
        $salida = new DateTimeImmutable('2026-08-02 10:00:00', $this->tz);

        $this->assertSame(1, ReglasReserva::calcularDias($entrada, $salida));
    }

    public function test_fraccion_de_dia_redondea_hacia_arriba(): void
    {
        $entrada = new DateTimeImmutable('2026-08-01 10:00:00', $this->tz);
        $salida = new DateTimeImmutable('2026-08-02 11:00:00', $this->tz);

        // 1 día y 1 hora -> ceil -> 2 días
        $this->assertSame(2, ReglasReserva::calcularDias($entrada, $salida));
    }

    public function test_multiples_dias(): void
    {
        $entrada = new DateTimeImmutable('2026-08-01 08:00:00', $this->tz);
        $salida = new DateTimeImmutable('2026-08-11 08:00:00', $this->tz);

        $this->assertSame(10, ReglasReserva::calcularDias($entrada, $salida));
    }

    public function test_salida_anterior_a_entrada_devuelve_cero(): void
    {
        $entrada = new DateTimeImmutable('2026-08-05 10:00:00', $this->tz);
        $salida = new DateTimeImmutable('2026-08-01 10:00:00', $this->tz);

        $this->assertSame(0, ReglasReserva::calcularDias($entrada, $salida));
    }

    public function test_mismo_instante_devuelve_cero(): void
    {
        $fecha = new DateTimeImmutable('2026-08-01 10:00:00', $this->tz);

        $this->assertSame(0, ReglasReserva::calcularDias($fecha, $fecha));
    }
}
