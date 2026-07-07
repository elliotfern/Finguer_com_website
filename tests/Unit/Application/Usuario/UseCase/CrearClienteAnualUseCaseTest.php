<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\UseCase\CrearClienteAnualUseCase;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PDO;
use PHPUnit\Framework\TestCase;

final class CrearClienteAnualUseCaseTest extends TestCase
{
    private function inputValido(): array
    {
        return [
            'email' => 'nuevo.cliente@example.com',
            'nombre' => 'Juan Pérez',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2027-01-01',
            'matricula' => '1234ABC',
        ];
    }

    private function usuarioDe(string $email): Usuario
    {
        return Usuario::fromDatabase(
            uuid: UsuarioUuid::generate(),
            email: Email::fromString($email),
            estado: UsuarioEstado::Activo,
            rol: Rol::ClienteAnual,
            locale: Locale::Es,
            password: null,
        );
    }

    public function test_crea_usuario_perfil_y_abono_cuando_email_no_existe(): void
    {
        $usuarioRepo = $this->createMock(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createMock(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $usuarioRepo->method('findByEmail')->willReturn(null);
        // Tras crear el usuario, ActualizarPerfil comprueba su existencia por uuid:
        $usuarioRepo
            ->method('findByUuid')
            ->willReturn($this->usuarioDe('nuevo.cliente@example.com'));

        $usuarioRepo->expects($this->once())->method('save');
        $usuarioRepo->expects($this->once())->method('savePerfil');
        $abonoRepo->expects($this->once())->method('save');

        $conn->expects($this->once())->method('beginTransaction');
        $conn->expects($this->once())->method('commit');
        $conn->expects($this->never())->method('rollBack');

        $useCase = new CrearClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $usuario = $useCase->execute($this->inputValido());

        $this->assertInstanceOf(Usuario::class, $usuario);
        $this->assertSame(
            'nuevo.cliente@example.com',
            $usuario->email()->value(),
        );
    }

    public function test_reutiliza_usuario_existente_por_email(): void
    {
        $usuarioRepo = $this->createMock(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createMock(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $existente = $this->usuarioDe('nuevo.cliente@example.com');

        $usuarioRepo->method('findByEmail')->willReturn($existente);
        $usuarioRepo->method('findByUuid')->willReturn($existente);

        $usuarioRepo->expects($this->never())->method('save');
        $usuarioRepo->expects($this->once())->method('savePerfil');
        $abonoRepo->expects($this->once())->method('save');

        $conn->expects($this->once())->method('beginTransaction');
        $conn->expects($this->once())->method('commit');

        $useCase = new CrearClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $usuario = $useCase->execute($this->inputValido());

        $this->assertTrue($usuario->uuid()->equals($existente->uuid()));
    }

    public function test_hace_rollback_si_falla_la_creacion_del_abono(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $usuarioRepo->method('findByEmail')->willReturn(null);
        $usuarioRepo
            ->method('findByUuid')
            ->willReturn($this->usuarioDe('nuevo.cliente@example.com'));
        $abonoRepo
            ->method('save')
            ->willThrowException(new \RuntimeException('DB error simulado'));

        $conn->expects($this->once())->method('beginTransaction');
        $conn->expects($this->once())->method('rollBack');
        $conn->expects($this->never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $useCase = new CrearClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $useCase->execute($this->inputValido());
    }
}
