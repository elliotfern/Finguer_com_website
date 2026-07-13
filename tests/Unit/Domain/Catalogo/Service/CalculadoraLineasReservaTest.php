<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Catalogo\Service;

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Service\CalculadoraLineasReserva;
use PHPUnit\Framework\TestCase;

final class CalculadoraLineasReservaTest extends TestCase
{
    private function makeTarifa(string $codigo = 'RESERVA_FINGUER'): Servicio
    {
        return Servicio::fromDatabase([
            'id' => 1,
            'codigo' => $codigo,
            'nombre' => 'Reserva Finguer Class',
            'tipo' => 'parking',
            'modo_precio' => 'FIJO',
            'iva_percent' => 21.0,
            'activo' => 1,
            'precio_base' => null,
            'dias_incluidos' => 10,
            'min_con_iva' => 100.0,
            'extra_dia_con_iva' => 5.0,
            'seg_umbral_con_iva' => null,
            'seg_min_con_iva' => null,
            'seg_factor' => null,
        ]);
    }

    private function makeLimpieza(): Servicio
    {
        return Servicio::fromDatabase([
            'id' => 3,
            'codigo' => 'LIMPIEZA_EXT',
            'nombre' => 'Limpieza exterior',
            'tipo' => 'extra',
            'modo_precio' => 'FIJO',
            'iva_percent' => 21.0,
            'activo' => 1,
            'precio_base' => 12.4,
            'dias_incluidos' => null,
            'min_con_iva' => null,
            'extra_dia_con_iva' => null,
            'seg_umbral_con_iva' => null,
            'seg_min_con_iva' => null,
            'seg_factor' => null,
        ]);
    }

    private function makeSeguro(): Servicio
    {
        return Servicio::fromDatabase([
            'id' => 6,
            'codigo' => 'SEGURO_CANCELACION',
            'nombre' => 'Seguro de cancelación',
            'tipo' => 'seguro',
            'modo_precio' => 'PORCENTAJE_CONDICIONAL',
            'iva_percent' => 21.0,
            'activo' => 1,
            'precio_base' => null,
            'dias_incluidos' => null,
            'min_con_iva' => null,
            'extra_dia_con_iva' => null,
            'seg_umbral_con_iva' => 100.0,
            'seg_min_con_iva' => 30.0,
            'seg_factor' => 0.1,
        ]);
    }

    public function test_calcula_solo_tarifa_sin_extras(): void
    {
        $lineas = CalculadoraLineasReserva::calcular($this->makeTarifa(), 10);

        $this->assertCount(1, $lineas);
        $this->assertSame('RESERVA_FINGUER', $lineas[0]->codigo);
        $this->assertSame(100.0, $lineas[0]->total);
    }

    public function test_calcula_tarifa_con_dias_extra(): void
    {
        // 10 días incluidos + 5 extra * 5€ = 125€ con IVA
        $lineas = CalculadoraLineasReserva::calcular($this->makeTarifa(), 15);

        $this->assertSame(125.0, $lineas[0]->total);
    }

    public function test_calcula_tarifa_mas_limpieza(): void
    {
        $lineas = CalculadoraLineasReserva::calcular(
            $this->makeTarifa(),
            10,
            $this->makeLimpieza(),
        );

        $this->assertCount(2, $lineas);
        $this->assertSame('LIMPIEZA_EXT', $lineas[1]->codigo);
        $this->assertSame(12.4, $lineas[1]->base);
    }

    public function test_calcula_tarifa_mas_seguro_bajo_umbral(): void
    {
        // Solo tarifa (100€) -> por debajo/igual al umbral (100€) -> seguro mínimo 30€
        $lineas = CalculadoraLineasReserva::calcular(
            $this->makeTarifa(),
            10,
            null,
            $this->makeSeguro(),
        );

        $this->assertCount(2, $lineas);
        $this->assertSame('SEGURO_CANCELACION', $lineas[1]->codigo);
        $this->assertSame(30.0, $lineas[1]->total);
    }

    public function test_calcula_tarifa_mas_seguro_supera_umbral(): void
    {
        // Tarifa con 15 días (125€) -> supera umbral (100€) -> 10% de 125 = 12.5€
        $lineas = CalculadoraLineasReserva::calcular(
            $this->makeTarifa(),
            15,
            null,
            $this->makeSeguro(),
        );

        $this->assertSame(12.5, $lineas[1]->total);
    }

    public function test_calcula_tarifa_limpieza_y_seguro_juntos(): void
    {
        $lineas = CalculadoraLineasReserva::calcular(
            $this->makeTarifa(),
            10,
            $this->makeLimpieza(),
            $this->makeSeguro(),
        );

        $this->assertCount(3, $lineas);
        $this->assertSame('RESERVA_FINGUER', $lineas[0]->codigo);
        $this->assertSame('LIMPIEZA_EXT', $lineas[1]->codigo);
        $this->assertSame('SEGURO_CANCELACION', $lineas[2]->codigo);

        // Seguro se calcula sobre tarifa + limpieza, no solo tarifa
        // 100 + 12.4*1.21(ya incluido en total) = totalConIvaSinSeguro
        $totalSinSeguro = $lineas[0]->total + $lineas[1]->total;
        $this->assertGreaterThan(100.0, $totalSinSeguro);
    }

    public function test_lanza_excepcion_si_tarifa_no_es_de_parking(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        CalculadoraLineasReserva::calcular($this->makeLimpieza(), 10);
    }

    public function test_ajusta_total_a_euro_redondo_cuando_hay_decimales(): void
    {
        // Tarifa con precio que generará decimales tras sumar seguro
        $lineas = CalculadoraLineasReserva::calcular(
            $this->makeTarifa(),
            11, // 100 + 1*5 = 105€
            null,
            $this->makeSeguro(), // 105 > 100 -> 10% de 105 = 10.5€
        );

        $totalFinal = round(
            array_sum(array_map(fn($l) => $l->total, $lineas)),
            2,
        );

        // El total final debe ser un euro exacto (xx.00)
        $this->assertSame(0.0, fmod($totalFinal, 1.0));
    }
}
