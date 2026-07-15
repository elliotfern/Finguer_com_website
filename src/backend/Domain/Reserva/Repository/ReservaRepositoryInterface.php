<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Repository;

use App\Domain\Reserva\Entity\Reserva;

interface ReservaRepositoryInterface
{
    /**
     * Persiste la reserva y sus líneas de servicio en una transacción.
     * Devuelve la reserva con el id ya asignado por la BD.
     */
    public function save(Reserva $reserva): Reserva;
}
