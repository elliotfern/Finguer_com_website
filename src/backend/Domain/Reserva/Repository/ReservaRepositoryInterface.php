<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Repository;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\EstadoReserva;

interface ReservaRepositoryInterface
{
    /**
     * Persiste la reserva y sus líneas de servicio en una transacción.
     * Devuelve la reserva con el id ya asignado por la BD.
     */
    public function save(Reserva $reserva): Reserva;

    public function findById(int $id): ?Reserva;

    public function actualizarEstadoVehiculo(
        int $id,
        EstadoVehiculo $anterior,
        EstadoVehiculo $nuevo,
    ): void;

    public function actualizarEstado(
        int $id,
        EstadoReserva $anterior,
        EstadoReserva $nuevo,
    ): void;

    public function findByLocalizador(string $localizador): ?Reserva;

    public function actualizarDatosAnual(Reserva $reserva): void;

    public function actualizarDatosGenerales(Reserva $reserva): void;
}
