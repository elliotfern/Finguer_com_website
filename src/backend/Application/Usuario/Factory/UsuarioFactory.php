<?php

declare(strict_types=1);

namespace App\Application\Usuario\Factory;

use App\Application\Usuario\DTO\CrearUsuarioDTO;
use App\Application\Usuario\DTO\ActualizarPerfilDTO;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Domain\Usuario\ValueObjects\Nif;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;

final class UsuarioFactory
{
    public static function crear(CrearUsuarioDTO $dto): Usuario
    {
        return Usuario::create(
            uuid: UsuarioUuid::generate(),
            email: Email::fromString($dto->email),
            rol: $dto->rol,
            locale: $dto->locale,
            password: $dto->password,
        );
    }

    public static function crearPerfil(
        UsuarioUuid $usuarioUuid,
        ActualizarPerfilDTO $dto,
    ): Perfil {
        return Perfil::create(
            usuarioUuid: $usuarioUuid,
            nombre: NombreCompleto::fromString($dto->nombre),
            telefono: $dto->telefono
                ? Telefono::fromString($dto->telefono)
                : null,
            nif: $dto->nif ? Nif::fromString($dto->nif) : null,
            empresa: $dto->empresa,
            direccion: DireccionPostal::create(
                $dto->direccion,
                $dto->ciudad,
                $dto->codigoPostal,
                $dto->pais,
            ),
        );
    }

    public static function crearAbono(CrearAbonoDTO $dto): Abono
    {
        return Abono::create(
            id: UsuarioUuid::generate(),
            usuarioUuid: UsuarioUuid::fromString($dto->usuarioUuid),
            fechaInicio: new \DateTimeImmutable($dto->fechaInicio),
            fechaFin: new \DateTimeImmutable($dto->fechaFin),
            matricula: Matricula::fromString($dto->matricula),
            limiteReservas: $dto->limiteReservas,
            vehiculo: $dto->vehiculo,
            observaciones: $dto->observaciones,
        );
    }
}
