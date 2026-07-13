<?php

declare(strict_types=1);

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Enums\ModoPrecio;
use App\Domain\Catalogo\Enums\TipoServicio;
use PHPUnit\Framework\TestCase;

class ServicioTest extends TestCase
{
    private function makeParking(string $codigo = 'RESERVA_FINGUER'): Servicio
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

    private function makeExtra(): Servicio
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

    public function test_servicio_parking(): void
    {
        $s = $this->makeParking();
        $this->assertTrue($s->esParking());
        $this->assertSame(TipoServicio::Parking, $s->tipo());
        $this->assertSame(ModoPrecio::Fijo, $s->modoPrecio());
    }

    public function test_precio_parking_10_dias(): void
    {
        $s = $this->makeParking();
        $this->assertSame(100.0, $s->calcularPrecioParking(10));
    }

    public function test_precio_parking_15_dias(): void
    {
        $s = $this->makeParking();
        $this->assertSame(125.0, $s->calcularPrecioParking(15));
    }

    public function test_precio_parking_5_dias(): void
    {
        $s = $this->makeParking();
        // Menos de 10 días → precio mínimo
        $this->assertSame(100.0, $s->calcularPrecioParking(5));
    }

    public function test_precio_extra(): void
    {
        $s = $this->makeExtra();
        $this->assertTrue($s->esExtra());
        $this->assertSame(12.4, $s->calcularPrecioExtra());
    }

    public function test_precio_seguro_aplica_minimo_cuando_esta_en_el_umbral_o_por_debajo(): void
    {
        $s = $this->makeSeguro();
        // Total <= umbral (100€) → siempre se cobra el mínimo fijo (30€)
        $this->assertSame(30.0, $s->calcularPrecioSeguro(80.0));
        $this->assertSame(30.0, $s->calcularPrecioSeguro(100.0)); // límite exacto incluido
    }

    public function test_precio_seguro_aplica_porcentaje_cuando_supera_el_umbral(): void
    {
        $s = $this->makeSeguro();
        // Total > umbral (100€) → se aplica el factor, sin comparar con el mínimo
        // 10% de 400€ = 40€
        $this->assertSame(40.0, $s->calcularPrecioSeguro(400.0));
    }

    public function test_precio_seguro_no_aplica_maximo_con_el_minimo_al_superar_umbral(): void
    {
        $s = $this->makeSeguro();
        // Total > umbral pero el 10% da menos que el mínimo (30€): no hay "suelo",
        // se aplica el factor tal cual, igual que el legacy.
        // 10% de 150€ = 15€ (aunque sea menor que el mínimo de 30€)
        $this->assertSame(15.0, $s->calcularPrecioSeguro(150.0));
    }
}
