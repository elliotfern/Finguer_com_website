<?php

declare(strict_types=1);

namespace App\Application\Catalogo\UseCase;

use App\Application\Catalogo\DTO\ObtenerHorasDisponiblesDTO;
use App\Domain\Catalogo\Rules\HorariosReserva;

final class ObtenerHorasDisponibles
{
    public function execute(ObtenerHorasDisponiblesDTO $dto): array
    {
        $fecha = new \DateTimeImmutable($dto->fecha);
        return HorariosReserva::horasDisponibles($dto->tipoReserva, $fecha);
    }
}
