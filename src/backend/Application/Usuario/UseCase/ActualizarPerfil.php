<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Application\Usuario\Schema\UsuarioSchema;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class ActualizarPerfil
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(string $uuidString, array $input): void
    {
        // 1. Validar entrada
        $data = SchemaProcessor::process(
            $input,
            UsuarioSchema::actualizarPerfil(),
        );

        // 2. Verificar que el usuario existe
        $uuid = UsuarioUuid::fromString($uuidString);
        $usuario = $this->usuarioRepository->findByUuid($uuid);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        // 3. Crear y persistir perfil
        $dto = ActualizarPerfilDTO::fromArray($data);
        $perfil = UsuarioFactory::crearPerfil($uuid, $dto);
        $this->usuarioRepository->savePerfil($perfil);
    }
}
