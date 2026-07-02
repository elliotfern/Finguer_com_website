<?php

declare(strict_types=1);

namespace App\Application\Catalogo\UseCase;

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;

final class ObtenerServicioPorCodigo
{
    public function __construct(
        private readonly ServicioRepositoryInterface $servicioRepository,
    ) {}

    public function execute(string $codigo): ?Servicio
    {
        return $this->servicioRepository->findByCodigo(
            CodigoServicio::fromString($codigo),
        );
    }
}
