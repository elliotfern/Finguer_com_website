<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\UseCase;

use App\Application\Usuario\UseCase\ActualizarClienteAnualUseCase;
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

final class ActualizarClienteAnualUseCaseTest extends TestCase
{
    private function usuarioExistente(): Usuario
    {
        return Usuario::fromDatabase(
            uuid: UsuarioUuid::generate(),
            email: Email::fromString('cliente@example.com'),
            estado: UsuarioEstado::Activo,
            rol: Rol::ClienteAnual,
            locale: Locale::Es,
            password: null,
        );
    }

    private function inputValido(string $uuid): array
    {
        return [
            'uuid' => $uuid,
            'nombre' => 'Juan Pérez',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2027-01-01',
            'matricula' => '1234ABC',
        ];
    }

    public function test_lanza_excepcion_si_falta_uuid(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $conn->expects($this->never())->method('beginTransaction');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('MISSING_UUID');

        $useCase = new ActualizarClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $useCase->execute(['nombre' => 'Juan']);
    }

    public function test_lanza_excepcion_si_usuario_no_existe(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class); // este sí lleva expects(never)

        $usuarioRepo->method('findByUuid')->willReturn(null);
        $conn->expects($this->never())->method('beginTransaction');

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('NOT_FOUND');

        $useCase = new ActualizarClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $useCase->execute(['uuid' => UsuarioUuid::generate()->toString()]);
    }

    public function test_actualiza_perfil_y_abono_cuando_usuario_existe(): void
    {
        $usuarioRepo = $this->createMock(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createMock(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $existente = $this->usuarioExistente();
        $usuarioRepo->method('findByUuid')->willReturn($existente);

        $usuarioRepo->expects($this->once())->method('savePerfil');
        $abonoRepo->expects($this->once())->method('save');
        $abonoRepo->method('findByUsuarioUuid')->willReturn([]);

        $conn->expects($this->once())->method('beginTransaction');
        $conn->expects($this->once())->method('commit');
        $conn->expects($this->never())->method('rollBack');

        $useCase = new ActualizarClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $usuario = $useCase->execute(
            $this->inputValido($existente->uuid()->toString()),
        );

        $this->assertTrue($usuario->uuid()->equals($existente->uuid()));
    }

    public function test_hace_rollback_si_falla_la_actualizacion_del_abono(): void
    {
        $usuarioRepo = $this->createStub(UsuarioRepositoryInterface::class);
        $abonoRepo = $this->createStub(AbonoRepositoryInterface::class);
        $conn = $this->createMock(PDO::class);

        $existente = $this->usuarioExistente();
        $usuarioRepo->method('findByUuid')->willReturn($existente);
        $abonoRepo->method('findByUsuarioUuid')->willReturn([]);
        $abonoRepo
            ->method('save')
            ->willThrowException(new \RuntimeException('DB error simulado'));

        $conn->expects($this->once())->method('beginTransaction');
        $conn->expects($this->once())->method('rollBack');
        $conn->expects($this->never())->method('commit');

        $this->expectException(\RuntimeException::class);

        $useCase = new ActualizarClienteAnualUseCase(
            $conn,
            $usuarioRepo,
            $abonoRepo,
        );
        $useCase->execute($this->inputValido($existente->uuid()->toString()));
    }
}
