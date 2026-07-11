<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Usuario\DTO;

use App\Application\Usuario\DTO\UsuarioListItemDTO;
use App\Application\Usuario\DTO\UsuarioListResult;
use PHPUnit\Framework\TestCase;

final class UsuarioListResultTest extends TestCase
{
    private function makeItem(string $uuid): UsuarioListItemDTO
    {
        return new UsuarioListItemDTO(
            uuid: $uuid,
            nombre: 'Test User',
            email: 'test@example.com',
            telefono: '600000000',
            tipoRol: 'cliente',
            createdAt: '2026-07-01 10:00:00',
        );
    }

    public function test_to_array_with_items(): void
    {
        $items = [$this->makeItem('uuid-1'), $this->makeItem('uuid-2')];
        $result = new UsuarioListResult($items, 2);

        $array = $result->toArray();

        $this->assertSame(2, $array['total']);
        $this->assertTrue($array['hasRows']);
        $this->assertCount(2, $array['rows']);
        $this->assertSame('uuid-1', $array['rows'][0]['uuid']);
        $this->assertSame('uuid-2', $array['rows'][1]['uuid']);
    }

    public function test_to_array_with_empty_items(): void
    {
        $result = new UsuarioListResult([], 0);

        $array = $result->toArray();

        $this->assertSame(0, $array['total']);
        $this->assertFalse($array['hasRows']);
        $this->assertSame([], $array['rows']);
    }

    public function test_total_can_differ_from_items_count_due_to_pagination(): void
    {
        // Simula página parcial: 2 items mostrados de un total de 50
        $items = [$this->makeItem('uuid-1'), $this->makeItem('uuid-2')];
        $result = new UsuarioListResult($items, 50);

        $array = $result->toArray();

        $this->assertSame(50, $array['total']);
        $this->assertCount(2, $array['rows']);
        $this->assertTrue($array['hasRows']);
    }
}
