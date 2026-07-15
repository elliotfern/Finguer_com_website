<?php

declare(strict_types=1);

namespace App\Application\Carrito\UseCase;

use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;

final class ObtenerCarritoUseCase
{
    public function __construct(
        private readonly CarritoRepositoryInterface $carritoRepository,
    ) {}

    public function execute(string $session): Carrito
    {
        if (trim($session) === '') {
            throw new \InvalidArgumentException('MISSING_SESSION');
        }

        $carrito = $this->carritoRepository->findBySession($session);

        if ($carrito === null) {
            throw new \InvalidArgumentException('NOT_FOUND');
        }

        return $carrito;
    }
}
