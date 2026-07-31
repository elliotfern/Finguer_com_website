<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\UseCase\ObtenerClienteAnualUseCase;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;
use PHPUnit\Framework\TestCase;

final class ObtenerClienteAnualUseCaseTest extends TestCase
{
    private function makeUsuario(UsuarioUuid $uuid): Usuario
    {
        return Usuario::fromDatabase(
            $uuid,
            Email::fromString('cliente@finguer.com'),
            UsuarioEstado::Activo,
            Rol::ClienteAnual,
            Locale::Es,
            null,
            new \DateTimeImmutable('2025-01-15 10:00:00'),
            new \DateTimeImmutable('2025-01-15 10:00:00'),
        );
    }

    private function makePerfil(UsuarioUuid $uuid): Perfil
    {
        return Perfil::create(
            $uuid,
            NombreCompleto::fromString('Maria Garcia'),
            Telefono::fromString('+34600111222'),
            null,
            null,
            DireccionPostal::create('Calle Mayor 1', 'Terrassa', '08221'),
        );
    }

    private function makeAbono(UsuarioUuid $uuid): Abono
    {
        return Abono::create(
            id: UsuarioUuid::generate(),
            usuarioUuid: $uuid,
            fechaInicio: new \DateTimeImmutable('2026-01-01'),
            fechaFin: new \DateTimeImmutable('2027-01-01'),
            matricula: Matricula::fromString('1234ABC'),
            limiteReservas: 10,
            vehiculo: 'Seat Ibiza',
            observaciones: 'Cliente VIP',
        );
    }

    public function test_devuelve_dto_completo_con_abono(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);
        $perfil = $this->makePerfil($uuid);
        $abono = $this->makeAbono($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);
        $usuarioRepo->method('findPerfilByUuid')->willReturn($perfil);

        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $abonoRepo->method('findActivoByUsuarioUuid')->willReturn($abono);

        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);
        $dto = $useCase->execute($uuid->toString());

        $this->assertSame('Maria Garcia', $dto->nombre);
        $this->assertSame('cliente@finguer.com', $dto->email);
        $this->assertSame('2026-01-01', $dto->fechaInicio);
        $this->assertSame('2027-01-01', $dto->fechaFin);
        $this->assertSame(10, $dto->limiteReservas);
        $this->assertSame('Seat Ibiza', $dto->vehiculo);
        $this->assertSame('1234ABC', $dto->matricula);
        $this->assertSame('Cliente VIP', $dto->observaciones);
        $this->assertSame('activo', $dto->estado);
    }

    public function test_devuelve_dto_con_campos_de_abono_nulos_si_no_tiene_abono(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);
        $perfil = $this->makePerfil($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);
        $usuarioRepo->method('findPerfilByUuid')->willReturn($perfil);

        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $abonoRepo->method('findActivoByUsuarioUuid')->willReturn(null);

        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);
        $dto = $useCase->execute($uuid->toString());

        $this->assertSame('Maria Garcia', $dto->nombre); // usuario/perfil sí presentes
        $this->assertNull($dto->fechaInicio);
        $this->assertNull($dto->fechaFin);
        $this->assertNull($dto->limiteReservas);
        $this->assertNull($dto->vehiculo);
        $this->assertNull($dto->matricula);
        $this->assertNull($dto->estado);
    }

    public function test_devuelve_dto_con_nombre_vacio_si_no_hay_perfil(): void
    {
        $uuid = UsuarioUuid::generate();
        $usuario = $this->makeUsuario($uuid);

        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn($usuario);
        $usuarioRepo->method('findPerfilByUuid')->willReturn(null);

        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $abonoRepo->method('findActivoByUsuarioUuid')->willReturn(null);

        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);
        $dto = $useCase->execute($uuid->toString());

        $this->assertSame('', $dto->nombre);
        $this->assertNull($dto->telefono);
        $this->assertNull($dto->direccion);
    }

    public function test_lanza_excepcion_si_uuid_esta_vacio(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_UUID');

        $useCase->execute('');
    }

    public function test_lanza_excepcion_si_uuid_es_invalido(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BAD_UUID');

        $useCase->execute('no-es-un-uuid-valido');
    }

    public function test_lanza_excepcion_si_usuario_no_existe(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $usuarioRepo->method('findByUuid')->willReturn(null);

        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $useCase = new ObtenerClienteAnualUseCase($usuarioRepo, $abonoRepo);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NOT_FOUND');

        $useCase->execute(UsuarioUuid::generate()->toString());
    }
}
