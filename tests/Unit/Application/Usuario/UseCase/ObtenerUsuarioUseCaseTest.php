<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\UseCase\ObtenerUsuarioUseCase;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use PHPUnit\Framework\TestCase;

final class ObtenerUsuarioUseCaseTest extends TestCase
{
    public function test_devuelve_dto_completo_cuando_usuario_y_perfil_existen(): void
    {
        $uuid = UsuarioUuid::generate();
        $createdAt = new \DateTimeImmutable('2025-01-15 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-03-20 14:30:00');

        $usuario = Usuario::fromDatabase(
            $uuid,
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
            $createdAt,
            $updatedAt,
        );

        $perfil = Perfil::create(
            $uuid,
            NombreCompleto::fromString('Maria Garcia'),
            null,
            null,
            null,
            DireccionPostal::create('Calle Mayor 1', 'Terrassa', '08221'),
        );

        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByUuid')->willReturn($usuario);
        $repository->method('findPerfilByUuid')->willReturn($perfil);

        $useCase = new ObtenerUsuarioUseCase($repository);
        $dto = $useCase->execute($uuid->toString());

        $this->assertSame($uuid->toString(), $dto->uuid);
        $this->assertSame('Maria Garcia', $dto->nombre);
        $this->assertSame('test@finguer.com', $dto->email);
        $this->assertSame('activo', $dto->estado);
        $this->assertSame('Terrassa', $dto->ciudad);
        $this->assertSame('cliente', $dto->tipoRol);
        $this->assertSame('2025-01-15 10:00:00', $dto->createdAt);
        $this->assertSame('2026-03-20 14:30:00', $dto->updatedAt);
    }

    public function test_devuelve_dto_con_campos_vacios_cuando_no_hay_perfil(): void
    {
        $uuid = UsuarioUuid::generate();

        $usuario = Usuario::fromDatabase(
            $uuid,
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
        );

        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByUuid')->willReturn($usuario);
        $repository->method('findPerfilByUuid')->willReturn(null);

        $useCase = new ObtenerUsuarioUseCase($repository);
        $dto = $useCase->execute($uuid->toString());

        $this->assertSame('', $dto->nombre);
        $this->assertNull($dto->empresa);
        $this->assertNull($dto->direccion);
        $this->assertNull($dto->telefono);
    }

    public function test_lanza_excepcion_si_uuid_esta_vacio(): void
    {
        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $useCase = new ObtenerUsuarioUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_UUID');

        $useCase->execute('');
    }

    public function test_lanza_excepcion_si_uuid_es_invalido(): void
    {
        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $useCase = new ObtenerUsuarioUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('BAD_UUID');

        $useCase->execute('no-es-un-uuid-valido');
    }

    public function test_lanza_excepcion_si_usuario_no_existe(): void
    {
        $repository = $this->createStub(UsuarioRepositoryInterface::class);
        $repository->method('findByUuid')->willReturn(null);

        $useCase = new ObtenerUsuarioUseCase($repository);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NOT_FOUND');

        $useCase->execute(UsuarioUuid::generate()->toString());
    }
}
