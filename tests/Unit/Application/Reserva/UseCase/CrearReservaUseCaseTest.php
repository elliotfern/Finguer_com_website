<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\CrearReservaUseCase;
use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

final class CrearReservaUseCaseTest extends TestCase
{
    private function makeCarrito(): Carrito
    {
        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-07 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-11 10:00:00'),
        );

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
        ];

        return Carrito::crear('sess-123', $seleccion, 4, $lineas);
    }

    private function makeServicio(
        string $codigo = 'RESERVA_FINGUER',
        int $id = 1,
    ): Servicio {
        return Servicio::fromDatabase([
            'id' => $id,
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

    private function makeUsuarioActivo(UsuarioUuid $uuid): Usuario
    {
        return Usuario::fromDatabase(
            $uuid,
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
        );
    }

    private function baseInput(string $usuarioUuid): array
    {
        return [
            'session' => 'sess-123',
            'usuario_uuid' => $usuarioUuid,
            'localizador' => '0708261234',
            'vehiculo' => 'Seat Ibiza',
            'matricula' => '1234ABC',
            'vuelo' => 'IB1234',
            'numeroPersonas' => 2,
        ];
    }

    public function test_crea_reserva_correctamente_con_datos_validos(): void
    {
        $uuid = UsuarioUuid::generate();
        $carrito = $this->makeCarrito();
        $usuario = $this->makeUsuarioActivo($uuid);
        $servicio = $this->makeServicio();

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn($servicio);

        $reservaRepo = $this->createMock(ReservaRepositoryInterface::class);
        $reservaRepo
            ->expects($this->once())
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(1));

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $reserva = $useCase->execute($this->baseInput($uuid->toString()));

        $this->assertSame(1, $reserva->id());
        $this->assertSame('0708261234', $reserva->localizador());
        $this->assertSame(121.0, $reserva->totalCalculado());
        $this->assertCount(1, $reserva->lineas());
        $this->assertSame(1, $reserva->lineas()[0]->servicioId);
    }

    public function test_lanza_excepcion_si_carrito_no_existe(): void
    {
        $uuid = UsuarioUuid::generate();

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn(null);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('CARRITO_NOT_FOUND');

        $useCase->execute($this->baseInput($uuid->toString()));
    }

    public function test_lanza_excepcion_si_usuario_no_existe(): void
    {
        $uuid = UsuarioUuid::generate();
        $carrito = $this->makeCarrito();

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn(null);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('USUARIO_NO_VALIDO');

        $useCase->execute($this->baseInput($uuid->toString()));
    }

    public function test_lanza_excepcion_si_usuario_no_esta_activo(): void
    {
        $uuid = UsuarioUuid::generate();
        $carrito = $this->makeCarrito();

        $usuarioBloqueado = Usuario::fromDatabase(
            $uuid,
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Bloqueado,
            Rol::Cliente,
            Locale::Es,
            null,
        );

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuarioBloqueado);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('USUARIO_NO_VALIDO');

        $useCase->execute($this->baseInput($uuid->toString()));
    }

    public function test_lanza_excepcion_si_servicio_no_existe_en_catalogo(): void
    {
        $uuid = UsuarioUuid::generate();
        $carrito = $this->makeCarrito();
        $usuario = $this->makeUsuarioActivo($uuid);

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn(null);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/SERVICIO_NO_ENCONTRADO/');

        $useCase->execute($this->baseInput($uuid->toString()));
    }

    public function test_usa_el_localizador_recibido_sin_generarlo(): void
    {
        $uuid = UsuarioUuid::generate();
        $carrito = $this->makeCarrito();
        $usuario = $this->makeUsuarioActivo($uuid);
        $servicio = $this->makeServicio();

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn($servicio);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(1));

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $input = $this->baseInput($uuid->toString());
        $input['localizador'] = '0512269999'; // localizador arbitrario "externo"

        $reserva = $useCase->execute($input);

        $this->assertSame('0512269999', $reserva->localizador());
    }

    public function test_mapea_tipo_reserva_gold_class_correctamente(): void
    {
        $uuid = UsuarioUuid::generate();

        $seleccion = new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER_GOLD',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new \DateTimeImmutable('2026-08-07 10:00:00'),
            fechaSalida: new \DateTimeImmutable('2026-08-11 10:00:00'),
        );
        $lineas = [
            new LineaPrecio(
                'RESERVA_FINGUER_GOLD',
                'Reserva Gold',
                1.0,
                21.0,
                140.0,
                29.4,
                169.4,
            ),
        ];
        $carrito = Carrito::crear('sess-123', $seleccion, 4, $lineas);

        $usuario = $this->makeUsuarioActivo($uuid);
        $servicio = $this->makeServicio('RESERVA_FINGUER_GOLD', 2);

        $carritoRepo = $this->createStub(CarritoRepositoryInterface::class);
        $carritoRepo->method('findBySession')->willReturn($carrito);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);

        $servicioRepo = $this->createStub(ServicioRepositoryInterface::class);
        $servicioRepo->method('findByCodigo')->willReturn($servicio);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo
            ->method('save')
            ->willReturnCallback(fn(Reserva $r) => $r->conId(2));

        $useCase = new CrearReservaUseCase(
            $carritoRepo,
            $usuarioRepo,
            $servicioRepo,
            $reservaRepo,
        );

        $reserva = $useCase->execute($this->baseInput($uuid->toString()));

        $this->assertSame(TipoReserva::GoldClass, $reserva->tipo());
    }
}
