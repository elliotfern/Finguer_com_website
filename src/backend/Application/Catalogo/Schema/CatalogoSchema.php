<?php

declare(strict_types=1);

namespace App\Application\Catalogo\Schema;

class CatalogoSchema
{
    public static function horasDisponibles(): array
    {
        return [
            'tipo_reserva' => [
                'rules' =>
                    'required|string|in:RESERVA_FINGUER,RESERVA_FINGUER_GOLD',
                'label' => 'Tipo de reserva',
            ],
            'fecha' => [
                'rules' => 'required|date',
                'label' => 'Fecha',
            ],
        ];
    }
}
