<?php

declare(strict_types=1);

use App\Application\Usuario\UseCase\BuscarOCrearUsuario;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

class BuscarOCrearUsuarioTest extends TestCase
{
    private UsuarioRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $repo;
    private BuscarOCrearUsuario $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(UsuarioRepositoryInterface::class);
        $this->useCase = new BuscarOCrearUsuario($this->repo);
    }

    public function test_devuelve_usuario_existente(): void
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

        $usuario = $this->useCase->execute([
            'email' => 'existente@finguer.com',
        ]);

        $this->assertSame(
            $existente->uuid()->toString(),
            $usuario->uuid()->toString(),
        );
    }

    public function test_crea_usuario_si_no_existe(): void
    {
        $this->repo->method('findByEmail')->willReturn(null);
        $this->repo->expects($this->once())->method('save');

        $usuario = $this->useCase->execute(['email' => 'nuevo@finguer.com']);

        $this->assertInstanceOf(Usuario::class, $usuario);
        $this->assertSame('nuevo@finguer.com', $usuario->email()->value());
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_email_invalido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->useCase->execute(['email' => 'no-es-un-email']);
    }
}
