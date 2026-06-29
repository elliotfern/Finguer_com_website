<?php

declare(strict_types=1);

use App\Application\Usuario\UseCase\CrearAbono;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PHPUnit\Framework\TestCase;

class CrearAbonoTest extends TestCase
{
    private UsuarioRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $usuarioRepo;
    private AbonoRepositoryInterface&\PHPUnit\Framework\MockObject\MockObject $abonoRepo;
    private CrearAbono $useCase;
    private Usuario $usuario;

    protected function setUp(): void
    {
        $this->usuarioRepo = $this->createMock(
            UsuarioRepositoryInterface::class,
        );
        $this->abonoRepo = $this->createMock(AbonoRepositoryInterface::class);
        $this->useCase = new CrearAbono($this->usuarioRepo, $this->abonoRepo);
        $this->usuario = Usuario::fromDatabase(
            UsuarioUuid::generate(),
            Email::fromString('test@finguer.com'),
            UsuarioEstado::Activo,
            Rol::ClienteAnual,
            Locale::Es,
            null,
        );
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_crea_abono_correctamente(): void
    {
        $this->usuarioRepo->method('findByUuid')->willReturn($this->usuario);
        $this->abonoRepo->expects($this->once())->method('save');

        $uuid = $this->useCase->execute([
            'usuario_uuid' => $this->usuario->uuid()->toString(),
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'matricula' => '1234ABC',
        ]);

        $this->assertNotEmpty($uuid);
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_usuario_no_encontrado_lanza_excepcion(): void
    {
        $this->usuarioRepo->method('findByUuid')->willReturn(null);

        $this->expectException(\RuntimeException::class);

        $this->useCase->execute([
            'usuario_uuid' => UsuarioUuid::generate()->toString(),
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'matricula' => '1234ABC',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
    public function test_matricula_invalida_lanza_excepcion(): void
    {
        $this->usuarioRepo->method('findByUuid')->willReturn($this->usuario);

        $this->expectException(
            \App\Application\Shared\Schema\SchemaValidationException::class,
        );

        $this->useCase->execute([
            'usuario_uuid' => $this->usuario->uuid()->toString(),
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'matricula' => '',
        ]);
    }
}
