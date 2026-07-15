<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Reserva\Entity;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use App\Domain\Shared\UsuarioUuid;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class ReservaTest extends TestCase
{
    /**
     * @return ReservaServicioLinea[]
     */
    private function makeLineas(): array
    {
        return [
            new ReservaServicioLinea(
                servicioId: 1,
                descripcion: 'Reserva Finguer Class',
                cantidad: 1.0,
                precioUnitario: 100.0,
                impuestoPercent: 21.0,
                totalBase: 100.0,
                totalImpuesto: 21.0,
                totalLinea: 121.0,
            ),
        ];
    }

    public function test_crear_fija_estado_pendiente_y_canal_web(): void
    {
        $reserva = Reserva::crear(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            personas: 2,
            tipo: TipoReserva::FinguerClass,
            vuelo: 'IB1234',
            lineas: $this->makeLineas(),
        );

        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado());
        $this->assertSame(
            EstadoVehiculo::PendienteEntrada,
            $reserva->estadoVehiculo(),
        );
        $this->assertSame(CanalReserva::Web, $reserva->canal());
        $this->assertNull($reserva->id());
    }

    public function test_crear_fija_created_y_updated_at(): void
    {
        $reserva = Reserva::crear(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: null,
            matricula: null,
            personas: null,
            tipo: TipoReserva::FinguerClass,
            vuelo: null,
            lineas: [],
        );

        $this->assertNotNull($reserva->createdAt());
        $this->assertNotNull($reserva->updatedAt());
        $this->assertEquals($reserva->createdAt(), $reserva->updatedAt());
    }

    public function test_crear_almacena_lineas(): void
    {
        $lineas = $this->makeLineas();

        $reserva = Reserva::crear(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: null,
            matricula: null,
            personas: null,
            tipo: TipoReserva::FinguerClass,
            vuelo: null,
            lineas: $lineas,
        );

        $this->assertCount(1, $reserva->lineas());
        $this->assertSame($lineas[0], $reserva->lineas()[0]);
    }

    public function test_con_id_asigna_id_preservando_el_resto(): void
    {
        $reserva = Reserva::crear(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            personas: 2,
            tipo: TipoReserva::FinguerClass,
            vuelo: 'IB1234',
            lineas: $this->makeLineas(),
        );

        $conId = $reserva->conId(42);

        $this->assertNull($reserva->id());
        $this->assertSame(42, $conId->id());
        $this->assertSame($reserva->localizador(), $conId->localizador());
        $this->assertSame($reserva->totalCalculado(), $conId->totalCalculado());
        $this->assertCount(1, $conId->lineas());
    }

    public function test_from_database_reconstruye_todos_los_campos(): void
    {
        $usuarioUuid = UsuarioUuid::generate();
        $createdAt = new DateTimeImmutable('2026-07-01 09:00:00');
        $updatedAt = new DateTimeImmutable('2026-07-02 10:00:00');

        $reserva = Reserva::fromDatabase(
            id: 99,
            usuarioUuid: $usuarioUuid,
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Dentro,
            fechaReserva: new DateTimeImmutable('2026-07-01 09:00:00'),
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            personas: 2,
            tipo: TipoReserva::GoldClass,
            vuelo: 'IB1234',
            notas: 'Cliente frecuente',
            canal: CanalReserva::Manual,
            lineas: $this->makeLineas(),
            createdAt: $createdAt,
            updatedAt: $updatedAt,
        );

        $this->assertSame(99, $reserva->id());
        $this->assertSame(EstadoReserva::Pagada, $reserva->estado());
        $this->assertSame(EstadoVehiculo::Dentro, $reserva->estadoVehiculo());
        $this->assertSame(TipoReserva::GoldClass, $reserva->tipo());
        $this->assertSame(CanalReserva::Manual, $reserva->canal());
        $this->assertSame('Cliente frecuente', $reserva->notas());
        $this->assertSame($createdAt, $reserva->createdAt());
        $this->assertSame($updatedAt, $reserva->updatedAt());
    }
}
