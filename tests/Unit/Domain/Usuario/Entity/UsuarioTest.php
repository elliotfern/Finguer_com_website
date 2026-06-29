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

        $this->assertSame(UsuarioEstado::Pendiente, $usuario->estado());
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
}
