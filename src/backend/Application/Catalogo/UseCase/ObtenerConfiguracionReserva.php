<?php

declare(strict_types=1);

namespace App\Application\Catalogo\UseCase;

use App\Domain\Catalogo\Rules\HorariosReserva;
use App\Domain\Catalogo\Rules\ReglasReserva;

final class ObtenerConfiguracionReserva
{
    public function execute(): array
    {
        return [
            'fecha_minima' => ReglasReserva::fechaMinima(),
            'fecha_maxima' => ReglasReserva::FECHA_MAXIMA,
            'dias_antelacion_minima' => ReglasReserva::DIAS_ANTELACION_MINIMA,
            'fechas_no_disponibles' => ReglasReserva::fechasNoDisponibles(),
            'horas_finguer_class' => HorariosReserva::horasBase(
                HorariosReserva::TIPO_FINGUER_CLASS,
            ),
            'horas_gold_class' => HorariosReserva::horasBase(
                HorariosReserva::TIPO_GOLD_CLASS,
            ),
        ];
    }
}
