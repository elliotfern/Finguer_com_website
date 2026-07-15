<?php

declare(strict_types=1);

use App\Domain\Reserva\Enums\TipoReserva;
use PHPUnit\Framework\TestCase;

class TipoReservaTest extends TestCase
{
    public function test_mapea_reserva_finguer_a_finguer_class(): void
    {
        $this->assertSame(
            TipoReserva::FinguerClass,
            TipoReserva::fromCodigoServicio('RESERVA_FINGUER'),
        );
    }

    public function test_mapea_reserva_finguer_gold_a_gold_class(): void
    {
        $this->assertSame(
            TipoReserva::GoldClass,
            TipoReserva::fromCodigoServicio('RESERVA_FINGUER_GOLD'),
        );
    }

    public function test_mapea_reserva_cliente_anual_a_anual(): void
    {
        $this->assertSame(
            TipoReserva::Anual,
            TipoReserva::fromCodigoServicio('RESERVA_CLIENTE_ANUAL'),
        );
    }

    public function test_mapeo_no_distingue_mayusculas_minusculas(): void
    {
        $this->assertSame(
            TipoReserva::FinguerClass,
            TipoReserva::fromCodigoServicio('reserva_finguer'),
        );
    }

    public function test_codigo_desconocido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        TipoReserva::fromCodigoServicio('CODIGO_INEXISTENTE');
    }

    public function test_valores_enteros_coinciden_con_legacy(): void
    {
        // Estos valores deben coincidir con la columna `tipo` en parking_reservas
        $this->assertSame(1, TipoReserva::FinguerClass->value);
        $this->assertSame(2, TipoReserva::GoldClass->value);
        $this->assertSame(3, TipoReserva::Anual->value);
    }
}
