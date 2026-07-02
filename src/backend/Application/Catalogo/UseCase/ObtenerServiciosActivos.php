<?php

declare(strict_types=1);

namespace App\Application\Catalogo\UseCase;

use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;

final class ObtenerServiciosActivos
{
    public function __construct(
        private readonly ServicioRepositoryInterface $servicioRepository,
    ) {}

    public function execute(): array
    {
        return $this->servicioRepository->findAllActivos();
    }
}
