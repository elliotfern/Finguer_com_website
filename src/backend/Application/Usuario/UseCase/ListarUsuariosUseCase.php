<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\UsuarioListItemDTO;
use App\Application\Usuario\DTO\UsuarioListResult;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;
use App\Domain\Usuario\ValueObjects\UsuarioResumen;

final class ListarUsuariosUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $repository,
    ) {}

    public function execute(UsuarioListCriteria $criteria): UsuarioListResult
    {
        $listado = $this->repository->findByCriteria($criteria);

        $items = array_map(
            fn(UsuarioResumen $resumen) => new UsuarioListItemDTO(
                uuid: $resumen->uuid->toString(),
                nombre: $resumen->nombre,
                email: $resumen->email,
                telefono: $resumen->telefono,
                tipoRol: $resumen->rol->value,
                createdAt: $resumen->createdAt?->format('Y-m-d H:i:s'),
            ),
            $listado->items,
        );

        return new UsuarioListResult($items, $listado->total);
    }
}
