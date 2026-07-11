<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\DTO;

use App\Application\Usuario\DTO\UsuarioListItemDTO;
use PHPUnit\Framework\TestCase;

final class UsuarioListItemDTOTest extends TestCase
{
    public function test_to_array_returns_expected_shape(): void
    {
        $dto = new UsuarioListItemDTO(
            uuid: '018f2e2e-1234-7000-8000-abcdefabcdef',
            nombre: 'Maria Garcia',
            email: 'maria@example.com',
            telefono: '600111222',
            tipoRol: 'cliente',
            createdAt: '2026-07-01 10:00:00',
        );

        $this->assertSame(
            [
                'uuid' => '018f2e2e-1234-7000-8000-abcdefabcdef',
                'nombre' => 'Maria Garcia',
                'email' => 'maria@example.com',
                'telefono' => '600111222',
                'tipo_rol' => 'cliente',
                'createdAt' => '2026-07-01 10:00:00',
            ],
            $dto->toArray(),
        );
    }

    public function test_created_at_can_be_null(): void
    {
        $dto = new UsuarioListItemDTO(
            uuid: '018f2e2e-1234-7000-8000-abcdefabcdef',
            nombre: 'Maria Garcia',
            email: 'maria@example.com',
            telefono: '600111222',
            tipoRol: 'cliente',
            createdAt: null,
        );

        $this->assertNull($dto->toArray()['createdAt']);
    }
}
