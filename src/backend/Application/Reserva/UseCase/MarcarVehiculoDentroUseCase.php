<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;

final class MarcarVehiculoDentroUseCase
{
    public function __construct(
        private readonly ReservaRepositoryInterface $reservaRepository,
    ) {}

    public function execute(int $reservaId): void
    {
        $reserva = $this->reservaRepository->findById($reservaId);
        if ($reserva === null) {
            throw ReservaNotFoundException::porId($reservaId);
        }

        $estadoAnterior = $reserva->estadoVehiculo();
        $actualizada = $reserva->marcarVehiculoDentro();

        if ($actualizada->estadoVehiculo() === $estadoAnterior) {
            return; // idempotente: no hubo cambio real, no hace falta persistir
        }

        $this->reservaRepository->actualizarEstadoVehiculo(
            $reservaId,
            $estadoAnterior,
            $actualizada->estadoVehiculo(),
        );
    }
}
