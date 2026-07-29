<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Reserva\UseCase;

use App\Application\Reserva\UseCase\ObtenerReservaUseCase;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;
use PHPUnit\Framework\TestCase;

final class ObtenerReservaUseCaseTest extends TestCase
{
    private function makeReserva(UsuarioUuid $uuid): Reserva
    {
        return Reserva::fromDatabase(
            id: 1,
            usuarioUuid: $uuid,
            localizador: '0708261234',
            estado: EstadoReserva::Pagada,
            estadoVehiculo: EstadoVehiculo::Dentro,
            fechaReserva: new \DateTimeImmutable('2026-08-07 09:00:00'),
            entradaPrevista: new \DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new \DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            personas: 2,
            tipo: TipoReserva::FinguerClass,
            vuelo: 'IB1234',
            notas: 'Cliente frecuente',
            canal: CanalReserva::Web,
            lineas: [],
            createdAt: new \DateTimeImmutable(),
            updatedAt: new \DateTimeImmutable(),
        );
    }

    public function test_devuelve_dto_completo_con_perfil(): void
    {
        $uuid = UsuarioUuid::generate();
        $reserva = $this->makeReserva($uuid);

        $perfil = Perfil::create(
            $uuid,
            NombreCompleto::fromString('Maria Garcia'),
            Telefono::fromString('+34600111222'),
            null,
            null,
            DireccionPostal::create(null, null, null),
        );

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo->method('findById')->willReturn($reserva);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findPerfilByUuid')->willReturn($perfil);

        $useCase = new ObtenerReservaUseCase($reservaRepo, $usuarioRepo);
        $dto = $useCase->execute(1);

        $this->assertSame(1, $dto->id);
        $this->assertSame('0708261234', $dto->localizador);
        $this->assertSame('pagada', $dto->estado);
        $this->assertSame('dentro', $dto->estadoVehiculo);
        $this->assertSame(1, $dto->tipo);
        $this->assertSame('Maria Garcia', $dto->nombre);
        $this->assertSame('+34600111222', $dto->telefono);
    }

    public function test_devuelve_dto_con_nombre_y_telefono_nulos_sin_perfil(): void
    {
        $uuid = UsuarioUuid::generate();
        $reserva = $this->makeReserva($uuid);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo->method('findById')->willReturn($reserva);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findPerfilByUuid')->willReturn(null);

        $useCase = new ObtenerReservaUseCase($reservaRepo, $usuarioRepo);
        $dto = $useCase->execute(1);

        $this->assertNull($dto->nombre);
        $this->assertNull($dto->telefono);
    }

    public function test_lanza_excepcion_si_reserva_no_existe(): void
    {
        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo->method('findById')->willReturn(null);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);

        $useCase = new ObtenerReservaUseCase($reservaRepo, $usuarioRepo);

        $this->expectException(ReservaNotFoundException::class);

        $useCase->execute(999);
    }

    public function test_to_array_produce_las_claves_esperadas(): void
    {
        $uuid = UsuarioUuid::generate();
        $reserva = $this->makeReserva($uuid);

        $reservaRepo = $this->createStub(ReservaRepositoryInterface::class);
        $reservaRepo->method('findById')->willReturn($reserva);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findPerfilByUuid')->willReturn(null);

        $useCase = new ObtenerReservaUseCase($reservaRepo, $usuarioRepo);
        $array = $useCase->execute(1)->toArray();

        $this->assertArrayHasKey('usuario_uuid', $array);
        $this->assertArrayHasKey('estado_vehiculo', $array);
        $this->assertArrayHasKey('total_calculado', $array);
    }
}
