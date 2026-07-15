<?php

declare(strict_types=1);

namespace App\Application\Reserva\Schema;

class ReservaSchema
{
    public static function crear(): array
    {
        return [
            'session' => [
                'rules' => 'required|string',
                'label' => 'Sesión del carrito',
            ],
            'usuario_uuid' => [
                'rules' => 'required|uuid',
                'label' => 'Usuario',
            ],
            'localizador' => [
                'rules' => 'required|string|max:50',
                'label' => 'Localizador',
            ],
            'vehiculo' => [
                'rules' => 'required|string|max:100|regex:/^[a-zA-Z0-9\s]+$/',
                'label' => 'Modelo del vehículo',
            ],
            'matricula' => [
                'rules' => 'required|string|max:20',
                'label' => 'Matrícula',
            ],
            'vuelo' => [
                'rules' => 'required|string|max:30',
                'label' => 'Número de vuelo',
            ],
            'numeroPersonas' => [
                'rules' => 'required|int|min_value:1|max_value:9',
                'label' => 'Número de acompañantes',
            ],
        ];
    }
}
