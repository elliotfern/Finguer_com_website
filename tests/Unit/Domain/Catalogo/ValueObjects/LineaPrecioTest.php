<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Catalogo\ValueObjects;

use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use PHPUnit\Framework\TestCase;

final class LineaPrecioTest extends TestCase
{
    public function test_crea_linea_con_valores_dados(): void
    {
        $linea = new LineaPrecio(
            codigo: 'RESERVA_FINGUER',
            descripcion: 'Parking Finguer Class',
            cantidad: 1.0,
            ivaPercent: 21.0,
            base: 100.0,
            iva: 21.0,
            total: 121.0,
        );

        $this->assertSame('RESERVA_FINGUER', $linea->codigo);
        $this->assertSame('Parking Finguer Class', $linea->descripcion);
        $this->assertSame(100.0, $linea->base);
        $this->assertSame(21.0, $linea->iva);
        $this->assertSame(121.0, $linea->total);
    }

    public function test_con_base_recalcula_iva_y_total(): void
    {
        $linea = new LineaPrecio(
            codigo: 'RESERVA_FINGUER',
            descripcion: 'Parking Finguer Class',
            cantidad: 1.0,
            ivaPercent: 21.0,
            base: 100.0,
            iva: 21.0,
            total: 121.0,
        );

        $ajustada = $linea->conBase(99.99);

        $this->assertSame(99.99, $ajustada->base);
        $this->assertSame(21.0, $ajustada->iva);
        $this->assertSame(120.99, $ajustada->total);
    }

    public function test_con_base_no_muta_la_linea_original(): void
    {
        $linea = new LineaPrecio(
            codigo: 'RESERVA_FINGUER',
            descripcion: 'Parking Finguer Class',
            cantidad: 1.0,
            ivaPercent: 21.0,
            base: 100.0,
            iva: 21.0,
            total: 121.0,
        );

        $linea->conBase(50.0);

        // El objeto original no cambia (inmutabilidad)
        $this->assertSame(100.0, $linea->base);
        $this->assertSame(121.0, $linea->total);
    }

    public function test_con_base_redondea_a_dos_decimales(): void
    {
        $linea = new LineaPrecio(
            codigo: 'SEGURO_CANCELACION',
            descripcion: 'Seguro de cancelación',
            cantidad: 1.0,
            ivaPercent: 21.0,
            base: 30.0,
            iva: 6.3,
            total: 36.3,
        );

        $ajustada = $linea->conBase(33.333333);

        $this->assertSame(33.33, $ajustada->base);
    }

    public function test_desde_total_con_iva_calcula_base_y_iva_correctamente(): void
    {
        $linea = LineaPrecio::desdeTotalConIva(
            codigo: 'RESERVA_FINGUER',
            descripcion: 'Parking Finguer Class',
            cantidad: 1.0,
            totalConIva: 121.0,
            ivaPercent: 21.0,
        );

        $this->assertSame(100.0, $linea->base);
        $this->assertSame(21.0, $linea->iva);
        $this->assertSame(121.0, $linea->total);
    }

    public function test_desde_total_con_iva_con_seguro_cancelacion(): void
    {
        // Caso real del legacy: seguro de 30€ "con IVA" ya fijado
        $linea = LineaPrecio::desdeTotalConIva(
            codigo: 'SEGURO_CANCELACION',
            descripcion: 'Seguro de cancelación',
            cantidad: 1.0,
            totalConIva: 30.0,
            ivaPercent: 21.0,
        );

        $this->assertEqualsWithDelta(24.79, $linea->base, 0.01);
        $this->assertSame(30.0, $linea->total);
    }

    public function test_desde_base_sin_iva_calcula_iva_y_total_correctamente(): void
    {
        $linea = LineaPrecio::desdeBaseSinIva(
            codigo: 'LIMPIEZA_BASICA',
            descripcion: 'Limpieza básica',
            cantidad: 1.0,
            baseSinIva: 20.0,
            ivaPercent: 21.0,
        );

        $this->assertSame(20.0, $linea->base);
        $this->assertSame(4.2, $linea->iva);
        $this->assertSame(24.2, $linea->total);
    }

    public function test_desde_base_sin_iva_redondea_base_a_dos_decimales(): void
    {
        $linea = LineaPrecio::desdeBaseSinIva(
            codigo: 'LIMPIEZA_BASICA',
            descripcion: 'Limpieza básica',
            cantidad: 1.0,
            baseSinIva: 20.005,
            ivaPercent: 21.0,
        );

        $this->assertSame(20.01, $linea->base);
    }
}
