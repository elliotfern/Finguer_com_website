<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Enums;

enum AbonoEstado: string
{
    case Activo = 'activo';
    case Caducado = 'caducado';
    case Cancelado = 'cancelado';
    case Suspendido = 'suspendido';
}
