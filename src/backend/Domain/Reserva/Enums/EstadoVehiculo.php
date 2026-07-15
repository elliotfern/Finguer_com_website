<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Enums;

enum EstadoVehiculo: string
{
    case PendienteEntrada = 'pendiente_entrada';
    case Dentro = 'dentro';
    case Salido = 'salido';
}
