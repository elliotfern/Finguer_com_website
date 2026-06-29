<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\DTO\CrearUsuarioDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Usuario\Schema\UsuarioSchema;
use App\Domain\Shared\Email;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class CrearUsuario
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(array $input): string
    {
        // 1. Validar entrada
        $data = SchemaProcessor::process($input, UsuarioSchema::create());

        // 2. Buscar si ya existe por email
        $email = Email::fromString($data['email']);
        $existente = $this->usuarioRepository->findByEmail($email);

        if ($existente !== null) {
            return $existente->uuid()->toString();
        }

        // 3. Crear usuario
        $dto = CrearUsuarioDTO::fromArray($data);
        $usuario = UsuarioFactory::crear($dto);

        // 4. Crear perfil vacío si hay nombre
        if (!empty($input['nombre'])) {
            $perfilDto = ActualizarPerfilDTO::fromArray($input);
            $perfil = UsuarioFactory::crearPerfil($usuario->uuid(), $perfilDto);
            $this->usuarioRepository->savePerfil($perfil);
        }

        // 5. Persistir
        $this->usuarioRepository->save($usuario);

        return $usuario->uuid()->toString();
    }
}
