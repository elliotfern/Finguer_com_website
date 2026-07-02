<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use App\Application\Usuario\UseCase\LoginUserUseCase;
use App\Application\Shared\Exception\AuthException;
use App\Application\Usuario\Http\AuthCookieServiceInterface;
use App\Application\Usuario\Security\JwtServiceInterface;
use App\Application\Usuario\Security\PasswordVerifierInterface;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class LoginUserUseCaseTest extends TestCase
{
    private UsuarioRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $repo;
    private PasswordVerifierInterface&\PHPUnit\Framework\MockObject\MockObject $passwordVerifier;
    private JwtServiceInterface&\PHPUnit\Framework\MockObject\MockObject $jwtService;
    private AuthCookieServiceInterface&\PHPUnit\Framework\MockObject\MockObject $cookieService;

    private LoginUserUseCase $useCase;

    protected function setUp(): void
    {
        $this->repo = $this->createMock(UsuarioRepositoryInterface::class);
        $this->passwordVerifier = $this->createMock(
            PasswordVerifierInterface::class,
        );
        $this->jwtService = $this->createMock(JwtServiceInterface::class);
        $this->cookieService = $this->createMock(
            AuthCookieServiceInterface::class,
        );

        $this->useCase = new LoginUserUseCase(
            $this->repo,
            $this->passwordVerifier,
            $this->jwtService,
            $this->cookieService,
        );
    }

    public function test_login_exitoso(): void
    {
        $user = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('admin@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Admin,
            Locale::Es,
            'hashed-password',
        );

        $this->repo
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordVerifier
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->jwtService
            ->expects($this->once())
            ->method('generate')
            ->willReturn('fake-token');

        $this->cookieService
            ->expects($this->once())
            ->method('setToken')
            ->with('fake-token', $this->greaterThan(time()));

        $result = $this->useCase->execute('admin@finguer.com', '123456');

        $this->assertSame('success', $result['status']);
    }

    public function test_usuario_no_existe_lanza_excepcion(): void
    {
        $this->repo
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn(null);

        $this->passwordVerifier->expects($this->never())->method('verify');

        $this->jwtService->expects($this->never())->method('generate');

        $this->cookieService->expects($this->never())->method('setToken');

        $this->expectException(AuthException::class);

        $this->useCase->execute('noexiste@finguer.com', '123456');
    }

    public function test_password_incorrecto_lanza_excepcion(): void
    {
        $user = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('admin@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Admin,
            Locale::Es,
            'hashed-password',
        );

        $this->repo
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordVerifier
            ->expects($this->once())
            ->method('verify')
            ->willReturn(false);

        $this->jwtService->expects($this->never())->method('generate');

        $this->cookieService->expects($this->never())->method('setToken');

        $this->expectException(AuthException::class);

        $this->useCase->execute('admin@finguer.com', 'wrong');
    }

    public function test_rol_no_autorizado_lanza_excepcion(): void
    {
        $user = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('cliente@finguer.com'),
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            'hash',
        );

        $this->repo
            ->expects($this->once())
            ->method('findByEmail')
            ->willReturn($user);

        $this->passwordVerifier
            ->expects($this->once())
            ->method('verify')
            ->willReturn(true);

        $this->jwtService->expects($this->never())->method('generate');

        $this->cookieService->expects($this->never())->method('setToken');

        $this->expectException(AuthException::class);

        $this->useCase->execute('cliente@finguer.com', '123456');
    }
}
