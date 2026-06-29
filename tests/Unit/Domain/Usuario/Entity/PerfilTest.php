<?php

declare(strict_types=1);

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\Nif;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;
use App\Domain\Usuario\Entity\Perfil;
use PHPUnit\Framework\TestCase;

class PerfilTest extends TestCase
{
    private UsuarioUuid $uuid;
    private NombreCompleto $nombre;

    protected function setUp(): void
    {
        $this->uuid = UsuarioUuid::generate();
        $this->nombre = NombreCompleto::fromString('Joan Miró');
    }

    public function test_perfil_minimo(): void
    {
        $perfil = Perfil::create($this->uuid, $this->nombre);

        $this->assertSame('Joan Miró', $perfil->nombre()->value());
        $this->assertNull($perfil->telefono());
        $this->assertNull($perfil->nif());
        $this->assertNull($perfil->empresa());
        $this->assertFalse($perfil->tieneDatosFacturacion());
    }

    public function test_perfil_completo_con_facturacion(): void
    {
        $perfil = Perfil::create(
            $this->uuid,
            $this->nombre,
            Telefono::fromString('+34 689 255 821'),
            Nif::fromString('12345678Z'),
            'Empresa SL',
            DireccionPostal::create(
                'Calle Mayor, 1',
                'Barcelona',
                '08001',
                'España',
            ),
        );

        $this->assertTrue($perfil->tieneDatosFacturacion());
        $this->assertSame('Empresa SL', $perfil->empresa());
    }

    public function test_perfil_sin_nif_no_tiene_datos_facturacion(): void
    {
        $perfil = Perfil::create(
            $this->uuid,
            $this->nombre,
            null,
            null,
            null,
            DireccionPostal::create(
                'Calle Mayor, 1',
                'Barcelona',
                '08001',
                'España',
            ),
        );

        $this->assertFalse($perfil->tieneDatosFacturacion());
    }
}
