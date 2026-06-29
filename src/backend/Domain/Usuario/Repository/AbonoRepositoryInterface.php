<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Repository;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;

interface AbonoRepositoryInterface
{
    public function findById(UsuarioUuid $id): ?Abono;

    public function findByUsuarioUuid(UsuarioUuid $usuarioUuid): array;

    public function findActivoByUsuarioUuid(UsuarioUuid $usuarioUuid): ?Abono;

    public function findActivoByMatricula(string $matricula): ?Abono;

    public function save(Abono $abono): void;
}
