<?php

declare(strict_types=1);

namespace App\Application\Usuario\Schema;

class UsuarioSchema
{
    public static function create(): array
    {
        return [
            'email' => [
                'rules' => 'required|email|max:255',
                'label' => 'Email',
            ],
            'password' => [
                'rules' => 'string',
                'label' => 'Password',
            ],
            'tipo_rol' => [
                'rules' => 'string|in:cliente,cliente_anual,admin,trabajador',
                'label' => 'Rol',
            ],
            'locale' => [
                'rules' => 'string|in:ca,es,fr,en,it',
                'label' => 'Idioma',
            ],
        ];
    }

    public static function actualizarPerfil(): array
    {
        return [
            'nombre' => [
                'rules' => 'required|string|max:255',
                'label' => 'Nombre',
            ],
            'telefono' => [
                'rules' => 'string|max:20',
                'label' => 'Teléfono',
            ],
            'empresa' => [
                'rules' => 'string|max:255',
                'label' => 'Empresa',
            ],
            'nif' => [
                'rules' => 'string|max:30',
                'label' => 'NIF',
            ],
            'direccion' => [
                'rules' => 'string|max:255',
                'label' => 'Dirección',
            ],
            'ciudad' => [
                'rules' => 'string|max:100',
                'label' => 'Ciudad',
            ],
            'codigo_postal' => [
                'rules' => 'string|max:10',
                'label' => 'Código postal',
            ],
            'pais' => [
                'rules' => 'string|max:50',
                'label' => 'País',
            ],
        ];
    }

    public static function crearAbono(): array
    {
        return [
            'usuario_uuid' => [
                'rules' => 'required|uuid',
                'label' => 'Usuario',
            ],
            'fecha_inicio' => [
                'rules' => 'required|date',
                'label' => 'Fecha inicio',
            ],
            'fecha_fin' => [
                'rules' => 'required|date',
                'label' => 'Fecha fin',
            ],
            'matricula' => [
                'rules' => 'required|string|max:20',
                'label' => 'Matrícula',
            ],
            'vehiculo' => [
                'rules' => 'string|max:100',
                'label' => 'Vehículo',
            ],
            'limite_reservas' => [
                'rules' => 'int',
                'label' => 'Límite reservas',
            ],
            'observaciones' => [
                'rules' => 'string',
                'label' => 'Observaciones',
            ],
        ];
    }
}
