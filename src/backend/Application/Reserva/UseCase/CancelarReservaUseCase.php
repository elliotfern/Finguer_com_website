<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\VerificadorFacturaInterface;

final class CancelarReservaUseCase
{
    public function __construct(
        private readonly ReservaRepositoryInterface $reservaRepository,
        private readonly VerificadorFacturaInterface $verificadorFactura,
    ) {}

    public function execute(int $reservaId): void
    {
        $reserva = $this->reservaRepository->findById($reservaId);
        if ($reserva === null) {
            throw ReservaNotFoundException::porId($reservaId);
        }

        if ($this->verificadorFactura->existeFacturaParaReserva($reservaId)) {
            throw new ReservaConFacturaException($reservaId);
        }

        $estadoAnterior = $reserva->estado();
        $cancelada = $reserva->cancelar();

        if ($cancelada->estado() === $estadoAnterior) {
            return;
        }

        $this->reservaRepository->actualizarEstado(
            $reservaId,
            $estadoAnterior,
            $cancelada->estado(),
        );
    }
}
