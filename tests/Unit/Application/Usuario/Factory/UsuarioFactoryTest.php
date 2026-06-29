<?php

declare(strict_types=1);

use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Application\Usuario\DTO\CrearUsuarioDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\AbonoEstado;
use PHPUnit\Framework\TestCase;

class UsuarioFactoryTest extends TestCase
{
    public function test_crear_usuario(): void
    {
        $dto = CrearUsuarioDTO::fromArray([
            'email' => 'test@finguer.com',
        ]);

        $usuario = UsuarioFactory::crear($dto);

        $this->assertInstanceOf(Usuario::class, $usuario);
        $this->assertSame('test@finguer.com', $usuario->email()->value());
        $this->assertFalse($usuario->hasPassword());
    }

    public function test_crear_perfil(): void
    {
        $uuid = UsuarioUuid::generate();
        $dto = ActualizarPerfilDTO::fromArray([
            'nombre' => 'Joan Miró',
            'telefono' => '+34 689 255 821',
        ]);

        $perfil = UsuarioFactory::crearPerfil($uuid, $dto);

        $this->assertInstanceOf(Perfil::class, $perfil);
        $this->assertSame('Joan Miró', $perfil->nombre()->value());
        $this->assertFalse($perfil->tieneDatosFacturacion());
    }

    public function test_crear_abono(): void
    {
        $uuid = UsuarioUuid::generate();
        $dto = CrearAbonoDTO::fromArray([
            'usuario_uuid' => $uuid->toString(),
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
            'matricula' => '1234ABC',
        ]);

        $abono = UsuarioFactory::crearAbono($dto);

        $this->assertInstanceOf(Abono::class, $abono);
        $this->assertSame(AbonoEstado::Activo, $abono->estado());
        $this->assertSame('1234ABC', $abono->matricula()->value());
    }
}
