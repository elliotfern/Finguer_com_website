<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Application\Reserva\DTO\ObtenerReservaDTO;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class ObtenerReservaUseCase
{
    public function __construct(
        private readonly ReservaRepositoryInterface $reservaRepository,
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(int $id): ObtenerReservaDTO
    {
        $reserva = $this->reservaRepository->findById($id);
        if ($reserva === null) {
            throw ReservaNotFoundException::porId($id);
        }

        $perfil = $this->usuarioRepository->findPerfilByUuid(
            $reserva->usuarioUuid(),
        );

        return new ObtenerReservaDTO(
            id: $reserva->id(),
            usuarioUuid: $reserva->usuarioUuid()->toString(),
            localizador: $reserva->localizador(),
            salidaPrevista: $reserva->salidaPrevista()->format('Y-m-d H:i:s'),
            entradaPrevista: $reserva->entradaPrevista()->format('Y-m-d H:i:s'),
            matricula: $reserva->matricula(),
            vehiculo: $reserva->vehiculo(),
            vuelo: $reserva->vuelo(),
            tipo: $reserva->tipo()->value,
            notas: $reserva->notas(),
            canal: $reserva->canal()->value,
            personas: $reserva->personas(),
            fechaReserva: $reserva->fechaReserva()->format('Y-m-d H:i:s'),
            estado: $reserva->estado()->value,
            estadoVehiculo: $reserva->estadoVehiculo()->value,
            subtotalCalculado: $reserva->subtotalCalculado(),
            ivaCalculado: $reserva->ivaCalculado(),
            totalCalculado: $reserva->totalCalculado(),
            nombre: $perfil?->nombre()->value(),
            telefono: $perfil?->telefono()?->value(),
        );
    }
}
