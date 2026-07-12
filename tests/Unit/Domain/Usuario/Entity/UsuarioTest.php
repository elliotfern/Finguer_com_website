<?php

declare(strict_types=1);

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Entity\Usuario;
use PHPUnit\Framework\TestCase;

class UsuarioTest extends TestCase
{
    private UsuarioUuid $uuid;
    private Email $email;

    protected function setUp(): void
    {
        $this->uuid = UsuarioUuid::generate();
        $this->email = Email::fromString('test@finguer.com');
    }

    public function test_crear_usuario_nuevo(): void
    {
        $usuario = Usuario::create($this->uuid, $this->email);
        $this->assertSame(UsuarioEstado::Activo, $usuario->estado()); // ← Pendiente → Activo
        $this->assertSame(Rol::Cliente, $usuario->rol());
        $this->assertSame(Locale::Es, $usuario->locale());
        $this->assertFalse($usuario->hasPassword());
    }

    public function test_usuario_admin(): void
    {
        $usuario = Usuario::create($this->uuid, $this->email, Rol::Admin);
        $this->assertTrue($usuario->esAdmin());
        $this->assertTrue($usuario->esTrabajador());
        $this->assertFalse($usuario->esCliente());
    }

    public function test_usuario_cliente(): void
    {
        $usuario = Usuario::create($this->uuid, $this->email, Rol::Cliente);
        $this->assertTrue($usuario->esCliente());
        $this->assertFalse($usuario->esAdmin());
    }

    public function test_usuario_con_password(): void
    {
        $usuario = Usuario::create(
            $this->uuid,
            $this->email,
            Rol::Cliente,
            Locale::Es,
            'hash123',
        );
        $this->assertTrue($usuario->hasPassword());
    }

    public function test_restaurar_desde_bd(): void
    {
        $usuario = Usuario::fromDatabase(
            $this->uuid,
            $this->email,
            UsuarioEstado::Activo,
            Rol::Admin,
            Locale::Ca,
            null,
        );
        $this->assertSame(UsuarioEstado::Activo, $usuario->estado());
        $this->assertSame(Rol::Admin, $usuario->rol());
        $this->assertSame(Locale::Ca, $usuario->locale());
    }

    public function test_crear_usuario_nuevo_fija_created_y_updated_at(): void
    {
        $usuario = Usuario::create($this->uuid, $this->email);

        $this->assertNotNull($usuario->createdAt());
        $this->assertNotNull($usuario->updatedAt());
        $this->assertEquals($usuario->createdAt(), $usuario->updatedAt());
    }

    public function test_restaurar_desde_bd_sin_fechas_devuelve_null(): void
    {
        $usuario = Usuario::fromDatabase(
            $this->uuid,
            $this->email,
            UsuarioEstado::Activo,
            Rol::Admin,
            Locale::Ca,
            null,
        );

        $this->assertNull($usuario->createdAt());
        $this->assertNull($usuario->updatedAt());
    }

    public function test_restaurar_desde_bd_con_fechas(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-15 10:00:00');
        $updatedAt = new \DateTimeImmutable('2026-03-20 14:30:00');

        $usuario = Usuario::fromDatabase(
            $this->uuid,
            $this->email,
            UsuarioEstado::Activo,
            Rol::Admin,
            Locale::Ca,
            null,
            $createdAt,
            $updatedAt,
        );

        $this->assertSame($createdAt, $usuario->createdAt());
        $this->assertSame($updatedAt, $usuario->updatedAt());
    }

    public function test_bloquear_preserva_created_at_y_refresca_updated_at(): void
    {
        $createdAt = new \DateTimeImmutable('2025-01-15 10:00:00');
        $updatedAt = new \DateTimeImmutable('2025-01-15 10:00:00');

        $usuario = Usuario::fromDatabase(
            $this->uuid,
            $this->email,
            UsuarioEstado::Activo,
            Rol::Cliente,
            Locale::Es,
            null,
            $createdAt,
            $updatedAt,
        );

        $bloqueado = $usuario->bloquear();

        $this->assertSame($createdAt, $bloqueado->createdAt());
        $this->assertNotSame($updatedAt, $bloqueado->updatedAt());
        $this->assertGreaterThan($updatedAt, $bloqueado->updatedAt());
    }
}
