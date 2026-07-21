<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Service;

interface VerificadorFacturaInterface
{
    public function existeFacturaParaReserva(int $reservaId): bool;
}
