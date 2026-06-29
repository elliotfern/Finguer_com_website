<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Repository;

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Entity\Perfil;

interface UsuarioRepositoryInterface
{
    public function findByUuid(UsuarioUuid $uuid): ?Usuario;

    public function findByEmail(Email $email): ?Usuario;

    public function findPerfilByUuid(UsuarioUuid $uuid): ?Perfil;

    public function save(Usuario $usuario): void;

    public function savePerfil(Perfil $perfil): void;

    public function existsEmail(Email $email): bool;
}
