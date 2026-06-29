<?php

declare(strict_types=1);

use App\Application\Usuario\UseCase\CrearUsuario;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CrearUsuarioTest extends TestCase
{
    private UsuarioRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $repo;
    private CrearUsuario $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(UsuarioRepositoryInterface::class);
        $this->useCase = new CrearUsuario($this->repo);
    }

    public function test_crea_usuario_nuevo(): void
    {
        $this->repo->method('findByEmail')->willReturn(null);
        $this->repo->expects($this->once())->method('save');

        $uuid = $this->useCase->execute(['email' => 'nuevo@finguer.com']);

        $this->assertNotEmpty($uuid);
    }

    public function test_devuelve_uuid_existente_si_email_ya_existe(): void
    {
        $existente = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('existente@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
        );

        $this->repo->method('findByEmail')->willReturn($existente);
        $this->repo->expects($this->never())->method('save');

        $uuid = $this->useCase->execute(['email' => 'existente@finguer.com']);

        $this->assertSame($existente->uuid()->toString(), $uuid);
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_email_invalido_lanza_excepcion(): void
    {
        $this->expectException(
            \App\Application\Shared\Schema\SchemaValidationException::class,
        );

        $this->useCase->execute(['email' => 'no-es-un-email']);
    }
}
