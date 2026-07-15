<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Enums;

enum CanalReserva: string
{
    case Web = '1';
    case Manual = '2';
    case Anual = '5';
}
