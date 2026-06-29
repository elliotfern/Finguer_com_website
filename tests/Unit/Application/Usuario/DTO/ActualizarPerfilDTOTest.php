<?php

declare(strict_types=1);

use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use PHPUnit\Framework\TestCase;

class ActualizarPerfilDTOTest extends TestCase
{
    public function test_crear_desde_array_completo(): void
    {
        $dto = ActualizarPerfilDTO::fromArray([
            'nombre' => 'Joan Miró',
            'telefono' => '+34 689 255 821',
            'empresa' => 'Empresa SL',
            'nif' => '12345678Z',
            'direccion' => 'Calle Mayor, 1',
            'ciudad' => 'Barcelona',
            'codigo_postal' => '08001',
            'pais' => 'España',
        ]);

        $this->assertSame('Joan Miró', $dto->nombre);
        $this->assertSame('+34 689 255 821', $dto->telefono);
        $this->assertSame('Empresa SL', $dto->empresa);
        $this->assertSame('12345678Z', $dto->nif);
        $this->assertSame('08001', $dto->codigoPostal);
    }

    public function test_campos_opcionales_son_null(): void
    {
        $dto = ActualizarPerfilDTO::fromArray([
            'nombre' => 'Joan Miró',
        ]);

        $this->assertNull($dto->telefono);
        $this->assertNull($dto->empresa);
        $this->assertNull($dto->nif);
        $this->assertNull($dto->direccion);
    }
}
