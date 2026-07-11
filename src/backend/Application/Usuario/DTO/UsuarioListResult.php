<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

final class UsuarioListResult
{
    /**
     * @param UsuarioListItemDTO[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {}

    public function toArray(): array
    {
        return [
            'total' => $this->total,
            'rows' => array_map(
                fn(UsuarioListItemDTO $item) => $item->toArray(),
                $this->items,
            ),
            'hasRows' => $this->items !== [],
        ];
    }
}
