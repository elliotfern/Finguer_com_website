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

    // Añadir a ReservaSchema.php existente

    public static function crearAnual(): array
    {
        return [
            'usuario_uuid' => [
                'rules' => 'required|uuid',
                'label' => 'Usuario',
            ],
            'diaEntrada' => [
                'rules' => 'required|date',
                'label' => 'Día de entrada',
            ],
            'horaEntrada' => [
                'rules' => 'required|string|max:5',
                'label' => 'Hora de entrada',
            ],
            'diaSalida' => [
                'rules' => 'string',
                'label' => 'Día de salida',
            ],
            'horaSalida' => [
                'rules' => 'string|max:5',
                'label' => 'Hora de salida',
            ],
            'vehiculo' => [
                'rules' => 'string|max:100',
                'label' => 'Vehículo',
            ],
            'matricula' => [
                'rules' => 'string|max:20',
                'label' => 'Matrícula',
            ],
            'vuelo' => [
                'rules' => 'string|max:30',
                'label' => 'Vuelo',
            ],
            'notes' => [
                'rules' => 'string',
                'label' => 'Notas',
            ],
        ];
    }

    // Añadir a ReservaSchema.php existente

    public static function actualizarAnual(): array
    {
        return [
            'localizador' => [
                'rules' => 'required|string|max:50',
                'label' => 'Localizador',
            ],
            'diaEntrada' => [
                'rules' => 'required|date',
                'label' => 'Día de entrada',
            ],
            'horaEntrada' => [
                'rules' => 'required|string|max:5',
                'label' => 'Hora de entrada',
            ],
            'diaSalida' => [
                'rules' => 'string',
                'label' => 'Día de salida',
            ],
            'horaSalida' => [
                'rules' => 'string|max:5',
                'label' => 'Hora de salida',
            ],
            'vehiculo' => [
                'rules' => 'string|max:100',
                'label' => 'Vehículo',
            ],
            'matricula' => [
                'rules' => 'string|max:20',
                'label' => 'Matrícula',
            ],
            'vuelo' => [
                'rules' => 'string|max:30',
                'label' => 'Vuelo',
            ],
            'notes' => [
                'rules' => 'string',
                'label' => 'Notas',
            ],
        ];
    }
}
