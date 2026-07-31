<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Usuario\DTO\ObtenerClienteAnualDTO;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class ObtenerClienteAnualUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly AbonoRepositoryInterface $abonoRepository,
    ) {}

    public function execute(string $uuidStr): ObtenerClienteAnualDTO
    {
        if (trim($uuidStr) === '') {
            throw new \InvalidArgumentException('MISSING_UUID');
        }

        try {
            $uuid = UsuarioUuid::fromString($uuidStr);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException('BAD_UUID');
        }

        $usuario = $this->usuarioRepository->findByUuid($uuid);
        if ($usuario === null) {
            throw new \InvalidArgumentException('NOT_FOUND');
        }

        $perfil = $this->usuarioRepository->findPerfilByUuid($uuid);
        $abono = $this->abonoRepository->findActivoByUsuarioUuid($uuid);

        return new ObtenerClienteAnualDTO(
            uuid: $usuario->uuid()->toString(),
            nombre: $perfil?->nombre()->value() ?? '',
            email: $usuario->email()->value(),
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
            fechaInicio: $abono?->fechaInicio()->format('Y-m-d'),
            fechaFin: $abono?->fechaFin()->format('Y-m-d'),
            limiteReservas: $abono?->limiteReservas(),
            vehiculo: $abono?->vehiculo(),
            matricula: $abono?->matricula()->value(),
            observaciones: $abono?->observaciones(),
            estado: $abono?->estado()->value,
        );
    }
}
