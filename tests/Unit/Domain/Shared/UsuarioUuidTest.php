<?php

declare(strict_types=1);

use App\Domain\Shared\UsuarioUuid;
use PHPUnit\Framework\TestCase;

class UsuarioUuidTest extends TestCase
{
    public function test_generar_uuid(): void
    {
        $uuid = UsuarioUuid::generate();
        $this->assertNotEmpty($uuid->toString());
    }

    public function test_desde_string_valido(): void
    {
        $uuid = UsuarioUuid::generate();
        $restored = UsuarioUuid::fromString($uuid->toString());
        $this->assertTrue($uuid->equals($restored));
    }

    public function test_desde_bytes_validos(): void
    {
        $uuid = UsuarioUuid::generate();
        $restored = UsuarioUuid::fromBytes($uuid->toBytes());
        $this->assertTrue($uuid->equals($restored));
    }

    public function test_string_invalido_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UsuarioUuid::fromString('no-es-un-uuid');
    }

    public function test_bytes_invalidos_lanza_excepcion(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        UsuarioUuid::fromBytes('corto');
    }

    public function test_dos_uuid_iguales(): void
    {
        $uuid = UsuarioUuid::generate();
        $other = UsuarioUuid::fromString($uuid->toString());
        $this->assertTrue($uuid->equals($other));
    }
}
