<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class UsuarioListado
{
    /**
     * @param UsuarioResumen[] $items
     */
    public function __construct(
        public readonly array $items,
        public readonly int $total,
    ) {}
}
