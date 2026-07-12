<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\ObtenerUsuarioDTO;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class ObtenerUsuarioUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $repository,
    ) {}

    public function execute(string $uuidStr): ObtenerUsuarioDTO
    {
        if (trim($uuidStr) === '') {
            throw new \InvalidArgumentException('MISSING_UUID');
        }

        try {
            $uuid = UsuarioUuid::fromString($uuidStr);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException('BAD_UUID');
        }

        $usuario = $this->repository->findByUuid($uuid);
        if ($usuario === null) {
            throw new \InvalidArgumentException('NOT_FOUND');
        }

        $perfil = $this->repository->findPerfilByUuid($uuid);

        return new ObtenerUsuarioDTO(
            uuid: $usuario->uuid()->toString(),
            nombre: $perfil?->nombre()->value() ?? '',
            email: $usuario->email()->value(),
            estado: $usuario->estado()->value,
            empresa: $perfil?->empresa(),
            nif: $perfil?->nif()?->value(),
            direccion: $perfil?->direccion()->direccion(),
            ciudad: $perfil?->direccion()->ciudad(),
            codigoPostal: $perfil?->direccion()->codigoPostal(),
            pais: $perfil?->direccion()->pais(),
            telefono: $perfil?->telefono()?->value(),
            tipoRol: $usuario->rol()->value,
            locale: $usuario->locale()->value,
            createdAt: $usuario->createdAt()?->format('Y-m-d H:i:s'),
            updatedAt: $usuario->updatedAt()?->format('Y-m-d H:i:s'),
        );
    }
}
