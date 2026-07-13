<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Catalogo\Service;

use App\Domain\Catalogo\Service\AjustadorTotalRedondo;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use PHPUnit\Framework\TestCase;

final class AjustadorTotalRedondoTest extends TestCase
{
    public function test_no_modifica_si_total_ya_es_redondo(): void
    {
        $lineas = [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Parking',
                1.0,
                21.0,
                100.0,
                21.0,
                121.0,
            ),
        ];

        $ajustadas = AjustadorTotalRedondo::ajustar($lineas);

        $this->assertSame(121.0, $ajustadas[0]->total);
        $this->assertSame(100.0, $ajustadas[0]->base);
    }

    public function test_ajusta_total_con_decimales_a_euro_redondo(): void
    {
        // 100.01 con 21% IVA da un total con céntimos sueltos
        $lineas = [
            LineaPrecio::desdeBaseSinIva(
                'RESERVA_FINGUER',
                'Parking',
                1.0,
                100.01,
                21.0,
            ),
        ];

        $totalAntes = $lineas[0]->total;
        $this->assertNotEquals(floor($totalAntes), $totalAntes);

        $ajustadas = AjustadorTotalRedondo::ajustar($lineas);

        $this->assertEquals(floor($totalAntes), $ajustadas[0]->total);
    }

    public function test_prioriza_codigo_de_reserva_para_el_ajuste(): void
    {
        $lineas = [
            LineaPrecio::desdeBaseSinIva(
                'SEGURO_CANCELACION',
                'Seguro',
                1.0,
                24.79,
                21.0,
            ),
            LineaPrecio::desdeBaseSinIva(
                'RESERVA_FINGUER',
                'Parking',
                1.0,
                100.01,
                21.0,
            ),
        ];

        $ajustadas = AjustadorTotalRedondo::ajustar($lineas, [
            'RESERVA_FINGUER',
            'RESERVA_FINGUER_GOLD',
        ]);

        // La línea de seguro no debe tocarse
        $this->assertSame(24.79, $ajustadas[0]->base);
        // La línea de parking sí se ajusta
        $this->assertNotEquals(100.01, $ajustadas[1]->base);
    }

    public function test_usa_primera_linea_si_no_hay_codigo_prioritario(): void
    {
        $lineas = [
            LineaPrecio::desdeBaseSinIva(
                'LIMPIEZA_BASICA',
                'Limpieza',
                1.0,
                50.01,
                21.0,
            ),
        ];

        $totalAntes = $lineas[0]->total;
        $ajustadas = AjustadorTotalRedondo::ajustar($lineas, [
            'RESERVA_FINGUER',
        ]);

        $this->assertEquals(floor($totalAntes), $ajustadas[0]->total);
    }

    public function test_respeta_iva_propio_de_cada_linea(): void
    {
        // Línea con IVA distinto al 21% habitual
        $lineas = [
            LineaPrecio::desdeBaseSinIva(
                'RESERVA_FINGUER',
                'Parking',
                1.0,
                100.01,
                10.0,
            ),
        ];

        $ajustadas = AjustadorTotalRedondo::ajustar($lineas);

        // El iva_percent de la línea ajustada debe mantenerse en 10%, no saltar a 21%
        $this->assertSame(10.0, $ajustadas[0]->ivaPercent);
    }

    public function test_no_modifica_lineas_originales_son_inmutables(): void
    {
        $original = LineaPrecio::desdeBaseSinIva(
            'RESERVA_FINGUER',
            'Parking',
            1.0,
            100.01,
            21.0,
        );
        $lineas = [$original];

        AjustadorTotalRedondo::ajustar($lineas);

        // El objeto original no cambia
        $this->assertSame(100.01, $original->base);
    }
}
