<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\UsuarioListResult;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\UsuarioListCriteria;

final class ListarUsuariosUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $repository,
    ) {}

    public function execute(UsuarioListCriteria $criteria): UsuarioListResult
    {
        return $this->repository->findByCriteria($criteria);
    }
}
