<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Enums;

enum TipoReserva: int
{
    case FinguerClass = 1;
    case GoldClass = 2;
    case Anual = 3;

    public static function fromCodigoServicio(string $codigo): self
    {
        return match (strtoupper($codigo)) {
            'RESERVA_FINGUER' => self::FinguerClass,
            'RESERVA_FINGUER_GOLD' => self::GoldClass,
            'RESERVA_CLIENTE_ANUAL' => self::Anual,
            default => throw new \InvalidArgumentException(
                "Código de servicio no mapea a ningún TipoReserva: {$codigo}",
            ),
        };
    }
}
