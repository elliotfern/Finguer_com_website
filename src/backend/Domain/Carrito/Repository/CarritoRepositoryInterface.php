<?php

declare(strict_types=1);

namespace App\Domain\Carrito\Repository;

use App\Domain\Carrito\Entity\Carrito;

interface CarritoRepositoryInterface
{
    public function findBySession(string $session): ?Carrito;

    public function save(Carrito $carrito): void;
}
