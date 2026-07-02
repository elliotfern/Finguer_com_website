<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Repository;

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;

interface ServicioRepositoryInterface
{
    public function findByCodigo(CodigoServicio $codigo): ?Servicio;

    public function findAllActivos(): array;

    public function findByTipo(string $tipo): array;
}
