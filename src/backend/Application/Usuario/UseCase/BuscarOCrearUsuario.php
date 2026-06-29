<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\CrearUsuarioDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Domain\Shared\Email;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class BuscarOCrearUsuario
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(array $input): Usuario
    {
        $email = Email::fromString($input['email'] ?? '');

        // 1. Buscar existente
        $existente = $this->usuarioRepository->findByEmail($email);
        if ($existente !== null) {
            return $existente;
        }

        // 2. Crear nuevo
        $dto = CrearUsuarioDTO::fromArray($input);
        $usuario = UsuarioFactory::crear($dto);
        $this->usuarioRepository->save($usuario);

        return $usuario;
    }
}
