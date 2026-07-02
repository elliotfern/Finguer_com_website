<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Enums;

enum TipoServicio: string
{
    case Parking = 'parking';
    case Extra = 'extra';
    case Seguro = 'seguro';
}
