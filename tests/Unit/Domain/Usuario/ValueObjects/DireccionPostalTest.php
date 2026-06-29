<?php

declare(strict_types=1);

use App\Domain\Usuario\ValueObjects\DireccionPostal;
use PHPUnit\Framework\TestCase;

class DireccionPostalTest extends TestCase
{
    public function test_direccion_completa(): void
    {
        $dir = DireccionPostal::create(
            'Carrer de l\'Alt Camp, 9',
            'Sant Boi de Llobregat',
            '08830',
            'España',
        );
        $this->assertSame('Carrer de l\'Alt Camp, 9', $dir->direccion());
        $this->assertSame('Sant Boi de Llobregat', $dir->ciudad());
        $this->assertSame('08830', $dir->codigoPostal());
        $this->assertSame('España', $dir->pais());
    }

    public function test_direccion_sin_datos_opcionales(): void
    {
        $dir = DireccionPostal::create(null, null, null);
        $this->assertNull($dir->direccion());
        $this->assertNull($dir->ciudad());
        $this->assertNull($dir->codigoPostal());
        $this->assertSame('España', $dir->pais());
    }

    public function test_sin_datos_facturacion(): void
    {
        $dir = DireccionPostal::create(null, null, null);
        $this->assertFalse($dir->tieneDatosFacturacion());
    }

    public function test_con_datos_facturacion(): void
    {
        $dir = DireccionPostal::create('Calle Mayor, 1', 'Barcelona', '08001');
        $this->assertTrue($dir->tieneDatosFacturacion());
    }

    public function test_pais_por_defecto_es_espana(): void
    {
        $dir = DireccionPostal::create(null, null, null);
        $this->assertSame('España', $dir->pais());
    }

    public function test_dos_direcciones_iguales(): void
    {
        $a = DireccionPostal::create(
            'Calle Mayor, 1',
            'Barcelona',
            '08001',
            'España',
        );
        $b = DireccionPostal::create(
            'Calle Mayor, 1',
            'Barcelona',
            '08001',
            'España',
        );
        $this->assertTrue($a->equals($b));
    }
}
