<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Exception;

final class ReservaConFacturaException extends \RuntimeException
{
    public function __construct(public readonly int $id)
    {
        parent::__construct(
            'No se puede cancelar: la reserva ya tiene una factura emitida',
        );
    }
}
