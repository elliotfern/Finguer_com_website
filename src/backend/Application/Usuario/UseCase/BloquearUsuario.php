<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class BloquearUsuario
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(string $uuidString): void
    {
        $uuid = UsuarioUuid::fromString($uuidString);
        $usuario = $this->usuarioRepository->findByUuid($uuid);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        $this->usuarioRepository->save($usuario->bloquear());
    }
}
