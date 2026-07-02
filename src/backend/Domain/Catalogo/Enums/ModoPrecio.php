<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Enums;

enum ModoPrecio: string
{
    case Fijo = 'FIJO';
    case PorcentajeCondicional = 'PORCENTAJE_CONDICIONAL';
}
