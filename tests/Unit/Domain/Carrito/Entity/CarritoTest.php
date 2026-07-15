<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Carrito\Entity;

use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use PHPUnit\Framework\TestCase;

final class CarritoTest extends TestCase
{
    private function makeSeleccion(): SeleccionReserva
    {
        return new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-01 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-05 10:00:00'),
        );
    }

    /**
     * @return LineaPrecio[]
     */
    private function makeLineas(): array
    {
        return [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                100.0,
                21.0,
                121.0,
            ),
        ];
    }

    public function test_crear_calcula_totales_correctamente(): void
    {
        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->assertSame('sess-123', $carrito->session());
        $this->assertSame(4, $carrito->diasReserva());
        $this->assertSame(100.0, $carrito->subtotalSinIva());
        $this->assertSame(21.0, $carrito->ivaTotal());
        $this->assertSame(121.0, $carrito->totalConIva());
    }

    public function test_crear_calcula_totales_con_varias_lineas(): void
    {
        $lineas = [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                100.0,
                21.0,
                121.0,
            ),
            new LineaPrecio(
                'LIMPIEZA_EXT',
                'Limpieza exterior',
                1.0,
                21.0,
                12.4,
                2.6,
                15.0,
            ),
        ];

        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $lineas,
        );

        $this->assertSame(112.4, $carrito->subtotalSinIva());
        $this->assertSame(23.6, $carrito->ivaTotal());
        $this->assertSame(136.0, $carrito->totalConIva());
    }

    public function test_crear_genera_hash_no_vacio(): void
    {
        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->assertNotEmpty($carrito->hash());
        $this->assertSame(64, strlen($carrito->hash())); // sha256 hex = 64 chars
    }

    public function test_mismo_contenido_genera_mismo_hash(): void
    {
        $carritoA = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );
        $carritoB = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->assertSame($carritoA->hash(), $carritoB->hash());
    }

    public function test_contenido_distinto_genera_hash_distinto(): void
    {
        $carritoA = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $lineasDistintas = [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                105.0,
                22.05,
                127.05,
            ),
        ];
        $carritoB = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $lineasDistintas,
        );

        $this->assertNotSame($carritoA->hash(), $carritoB->hash());
    }

    public function test_lineas_json_contiene_estructura_esperada(): void
    {
        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $json = json_decode($carrito->lineasJson(), true);

        $this->assertSame('EUR', $json['moneda']);
        $this->assertSame(4, $json['diasReserva']);
        $this->assertSame('RESERVA_FINGUER', $json['seleccion']['tipoReserva']);
        $this->assertSame('0', $json['seleccion']['limpieza']);
        $this->assertSame(0, $json['seleccion']['seguroCancelacion']);
        $this->assertCount(1, $json['lineas']);
        $this->assertEquals(121.0, $json['totales']['total_con_iva']);
    }

    public function test_from_database_reconstruye_sin_recalcular_hash(): void
    {
        $lineas = $this->makeLineas();
        $updatedAt = new \DateTimeImmutable('2026-07-01 12:00:00');

        $carrito = Carrito::fromDatabase(
            session: 'sess-123',
            seleccion: $this->makeSeleccion(),
            diasReserva: 4,
            lineas: $lineas,
            subtotalSinIva: 100.0,
            ivaTotal: 21.0,
            totalConIva: 121.0,
            hash: 'hash-arbitrario-almacenado',
            updatedAt: $updatedAt,
        );

        $this->assertSame('hash-arbitrario-almacenado', $carrito->hash());
        $this->assertSame($updatedAt, $carrito->updatedAt());
    }

    public function test_from_database_sin_updated_at_devuelve_null(): void
    {
        $carrito = Carrito::fromDatabase(
            session: 'sess-123',
            seleccion: $this->makeSeleccion(),
            diasReserva: 4,
            lineas: $this->makeLineas(),
            subtotalSinIva: 100.0,
            ivaTotal: 21.0,
            totalConIva: 121.0,
            hash: 'hash-x',
            updatedAt: null,
        );

        $this->assertNull($carrito->updatedAt());
    }

    public function test_to_snapshot_array_devuelve_estructura_completa(): void
    {
        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $snapshot = $carrito->toSnapshotArray();

        $this->assertSame('EUR', $snapshot['moneda']);
        $this->assertSame(4, $snapshot['diasReserva']);
        $this->assertSame(
            'RESERVA_FINGUER',
            $snapshot['seleccion']['tipoReserva'],
        );
        $this->assertSame('0', $snapshot['seleccion']['limpieza']);
        $this->assertSame(0, $snapshot['seleccion']['seguroCancelacion']);
        $this->assertCount(1, $snapshot['lineas']);
        $this->assertSame('RESERVA_FINGUER', $snapshot['lineas'][0]['codigo']);
        $this->assertEquals(121.0, $snapshot['totales']['total_con_iva']);
    }

    public function test_to_snapshot_json_coincide_con_lineas_json(): void
    {
        $carrito = Carrito::crear(
            'sess-123',
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->assertSame($carrito->lineasJson(), $carrito->toSnapshotJson());
    }
}
