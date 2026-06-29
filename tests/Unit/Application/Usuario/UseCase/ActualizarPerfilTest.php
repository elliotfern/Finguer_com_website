<?php

declare(strict_types=1);

use App\Application\Usuario\UseCase\ActualizarPerfil;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ActualizarPerfilTest extends TestCase
{
    private UsuarioRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $repo;
    private ActualizarPerfil $useCase;
    private Usuario $usuario;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(UsuarioRepositoryInterface::class);
        $this->useCase = new ActualizarPerfil($this->repo);
        $this->usuario = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
        );
    }

    public function test_actualiza_perfil_correctamente(): void
    {
        $this->repo->method('findByUuid')->willReturn($this->usuario);
        $this->repo->expects($this->once())->method('savePerfil');

        $this->useCase->execute($this->usuario->uuid()->toString(), [
            'nombre' => 'Joan Miró',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_usuario_no_encontrado_lanza_excepcion(): void
    {
        $this->repo->method('findByUuid')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->useCase->execute(UsuarioUuid::generate()->toString(), [
            'nombre' => 'Joan Miró',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_nombre_vacio_lanza_excepcion(): void
    {
        $this->repo->method('findByUuid')->willReturn($this->usuario);

        $this->expectException(
            \App\Application\Shared\Schema\SchemaValidationException::class,
        );

        $this->useCase->execute($this->usuario->uuid()->toString(), [
            'nombre' => '',
        ]);
    }
}
