<?php

declare(strict_types=1);

use App\Application\Usuario\DTO\CrearUsuarioDTO;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use PHPUnit\Framework\TestCase;

class CrearUsuarioDTOTest extends TestCase
{
    public function test_crear_desde_array_completo(): void
    {
        $dto = CrearUsuarioDTO::fromArray([
            'email' => 'test@finguer.com',
            'tipo_rol' => 'admin',
            'locale' => 'ca',
            'password' => 'hash123',
        ]);

        $this->assertSame('test@finguer.com', $dto->email);
        $this->assertSame(Rol::Admin, $dto->rol);
        $this->assertSame(Locale::Ca, $dto->locale);
        $this->assertSame('hash123', $dto->password);
    }

    public function test_valores_por_defecto(): void
    {
        $dto = CrearUsuarioDTO::fromArray([
            'email' => 'test@finguer.com',
        ]);

        $this->assertSame(Rol::Cliente, $dto->rol);
        $this->assertSame(Locale::Es, $dto->locale);
        $this->assertNull($dto->password);
    }

    public function test_rol_invalido_usa_cliente_por_defecto(): void
    {
        $dto = CrearUsuarioDTO::fromArray([
            'email' => 'test@finguer.com',
            'tipo_rol' => 'invalido',
        ]);

        $this->assertSame(Rol::Cliente, $dto->rol);
    }
}
