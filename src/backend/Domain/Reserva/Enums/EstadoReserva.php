<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Enums;

enum EstadoReserva: string
{
    case Pendiente = 'pendiente';
    case ProcesandoPago = 'procesando_pago';
    case PagoOficina = 'pago_oficina';
    case Pagada = 'pagada';
    case Cancelada = 'cancelada';
    case Anual = 'anual';
}
