<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Reserva\Entity;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\InvalidTransitionException;
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

    public function test_marcar_vehiculo_dentro_desde_pendiente_entrada(): void
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

        $actualizada = $reserva->marcarVehiculoDentro();

        $this->assertSame(
            EstadoVehiculo::Dentro,
            $actualizada->estadoVehiculo(),
        );
        $this->assertSame(
            EstadoVehiculo::PendienteEntrada,
            $reserva->estadoVehiculo(),
        );
    }

    public function test_marcar_vehiculo_dentro_es_idempotente_si_ya_esta_dentro(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Dentro,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $resultado = $reserva->marcarVehiculoDentro();

        $this->assertSame(EstadoVehiculo::Dentro, $resultado->estadoVehiculo());
    }

    public function test_marcar_vehiculo_dentro_falla_si_ya_esta_salido(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Salido,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->expectException(
            \App\Domain\Reserva\Exception\InvalidTransitionException::class,
        );

        $reserva->marcarVehiculoDentro();
    }

    public function test_marcar_vehiculo_salido_desde_dentro(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Dentro,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $actualizada = $reserva->marcarVehiculoSalido();

        $this->assertSame(
            EstadoVehiculo::Salido,
            $actualizada->estadoVehiculo(),
        );
    }

    public function test_marcar_vehiculo_salido_es_idempotente_si_ya_esta_salido(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Salido,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $resultado = $reserva->marcarVehiculoSalido();

        $this->assertSame(EstadoVehiculo::Salido, $resultado->estadoVehiculo());
    }

    public function test_marcar_vehiculo_salido_falla_si_esta_pendiente_entrada(): void
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

        $this->expectException(
            \App\Domain\Reserva\Exception\InvalidTransitionException::class,
        );

        $reserva->marcarVehiculoSalido();
    }

    public function test_cancelar_desde_pendiente(): void
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

        $cancelada = $reserva->cancelar();

        $this->assertSame(EstadoReserva::Cancelada, $cancelada->estado());
        $this->assertSame(EstadoReserva::Pendiente, $reserva->estado());
    }

    public function test_cancelar_es_idempotente_si_ya_esta_cancelada(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Cancelada,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $resultado = $reserva->cancelar();

        $this->assertSame(EstadoReserva::Cancelada, $resultado->estado());
    }

    public function test_cancelar_falla_si_esta_pagada(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $this->expectException(InvalidTransitionException::class);

        $reserva->cancelar();
    }

    public function test_cancelar_permite_desde_procesando_pago(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::ProcesandoPago,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $cancelada = $reserva->cancelar();

        $this->assertSame(EstadoReserva::Cancelada, $cancelada->estado());
    }

    public function test_crear_anual_fija_estado_anual_y_canal_anual(): void
    {
        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            vuelo: null,
            notas: null,
        );

        $this->assertSame(EstadoReserva::Anual, $reserva->estado());
        $this->assertSame(
            EstadoVehiculo::PendienteEntrada,
            $reserva->estadoVehiculo(),
        );
        $this->assertSame(CanalReserva::Anual, $reserva->canal());
        $this->assertSame(TipoReserva::Anual, $reserva->tipo());
        $this->assertNull($reserva->id());
    }

    public function test_crear_anual_sin_importes(): void
    {
        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );

        $this->assertNull($reserva->subtotalCalculado());
        $this->assertNull($reserva->ivaCalculado());
        $this->assertNull($reserva->totalCalculado());
    }

    public function test_crear_anual_usa_fecha_por_defecto_si_no_hay_salida(): void
    {
        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );

        $this->assertSame(
            '2030-01-01 00:00:00',
            $reserva->salidaPrevista()->format('Y-m-d H:i:s'),
        );
    }

    public function test_crear_anual_respeta_salida_explicita_si_se_indica(): void
    {
        $salida = new DateTimeImmutable('2027-06-15 12:00:00');

        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: $salida,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );

        $this->assertSame($salida, $reserva->salidaPrevista());
    }

    public function test_crear_anual_almacena_notas(): void
    {
        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: 'Cliente VIP',
        );

        $this->assertSame('Cliente VIP', $reserva->notas());
    }

    public function test_actualizar_datos_anual_correctamente(): void
    {
        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: null,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            vuelo: null,
            notas: null,
        );

        $nuevaEntrada = new DateTimeImmutable('2026-09-01 12:00:00');
        $actualizada = $reserva->actualizarDatosAnual(
            entradaPrevista: $nuevaEntrada,
            salidaPrevista: null,
            vehiculo: 'BMW Serie 3',
            matricula: '9999XYZ',
            vuelo: 'IB5678',
            notas: 'Cambio de vehículo',
        );

        $this->assertSame($nuevaEntrada, $actualizada->entradaPrevista());
        $this->assertSame('BMW Serie 3', $actualizada->vehiculo());
        $this->assertSame('9999XYZ', $actualizada->matricula());
        $this->assertSame('IB5678', $actualizada->vuelo());
        $this->assertSame('Cambio de vehículo', $actualizada->notas());
    }

    public function test_actualizar_datos_anual_falla_si_no_es_reserva_anual(): void
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

        $this->expectException(\DomainException::class);

        $reserva->actualizarDatosAnual(
            entradaPrevista: new DateTimeImmutable('2026-09-01 12:00:00'),
            salidaPrevista: null,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );
    }

    public function test_actualizar_datos_anual_mantiene_salida_si_no_se_indica_nueva(): void
    {
        $salidaOriginal = new DateTimeImmutable('2027-01-01 00:00:00');

        $reserva = Reserva::crearAnual(
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: $salidaOriginal,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );

        $actualizada = $reserva->actualizarDatosAnual(
            entradaPrevista: new DateTimeImmutable('2026-09-01 12:00:00'),
            salidaPrevista: null,
            vehiculo: null,
            matricula: null,
            vuelo: null,
            notas: null,
        );

        $this->assertSame($salidaOriginal, $actualizada->salidaPrevista());
    }

    public function test_forzar_estado_cambia_sin_validar_transicion(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pendiente,
            estadoVehiculo: EstadoVehiculo::PendienteEntrada,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        // Salto directo de Pendiente a Pagada, algo que cancelar() nunca permitiría
        $actualizada = $reserva->forzarEstado(EstadoReserva::Pagada);

        $this->assertSame(EstadoReserva::Pagada, $actualizada->estado());
    }

    public function test_actualizar_datos_generales_actualiza_todos_los_campos(): void
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
            lineas: [],
        );

        $nuevaEntrada = new DateTimeImmutable('2026-09-01 12:00:00');
        $nuevaSalida = new DateTimeImmutable('2026-09-05 12:00:00');

        $actualizada = $reserva->actualizarDatosGenerales(
            tipo: TipoReserva::GoldClass,
            canal: CanalReserva::Manual,
            entradaPrevista: $nuevaEntrada,
            salidaPrevista: $nuevaSalida,
            vehiculo: 'BMW Serie 3',
            matricula: '9999XYZ',
            personas: 3,
            vuelo: 'IB5678',
            notas: 'Cambio completo',
        );

        $this->assertSame(TipoReserva::GoldClass, $actualizada->tipo());
        $this->assertSame(CanalReserva::Manual, $actualizada->canal());
        $this->assertSame($nuevaEntrada, $actualizada->entradaPrevista());
        $this->assertSame($nuevaSalida, $actualizada->salidaPrevista());
        $this->assertSame('BMW Serie 3', $actualizada->vehiculo());
        $this->assertSame('9999XYZ', $actualizada->matricula());
        $this->assertSame(3, $actualizada->personas());
        $this->assertSame('IB5678', $actualizada->vuelo());
        $this->assertSame('Cambio completo', $actualizada->notas());
    }

    public function test_actualizar_datos_generales_no_toca_el_estado(): void
    {
        $reserva = Reserva::fromDatabase(
            id: 1,
            usuarioUuid: UsuarioUuid::generate(),
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Dentro,
            fechaReserva: new DateTimeImmutable('2026-08-07 09:00:00'),
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
            notas: null,
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );

        $actualizada = $reserva->actualizarDatosGenerales(
            tipo: TipoReserva::GoldClass,
            canal: CanalReserva::Manual,
            entradaPrevista: new DateTimeImmutable('2026-09-01 12:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-09-05 12:00:00'),
            vehiculo: null,
            matricula: null,
            personas: null,
            vuelo: null,
            notas: null,
        );

        $this->assertSame(EstadoReserva::Pagada, $actualizada->estado());
        $this->assertSame(
            EstadoVehiculo::Dentro,
            $actualizada->estadoVehiculo(),
        );
    }
}
