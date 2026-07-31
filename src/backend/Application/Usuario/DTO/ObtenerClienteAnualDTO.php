<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

final class ObtenerClienteAnualDTO
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $nombre,
        public readonly string $email,
        public readonly ?string $empresa,
        public readonly ?string $nif,
        public readonly ?string $direccion,
        public readonly ?string $ciudad,
        public readonly ?string $codigoPostal,
        public readonly ?string $pais,
        public readonly ?string $telefono,
        public readonly string $tipoRol,
        public readonly string $locale,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
        public readonly ?string $fechaInicio,
        public readonly ?string $fechaFin,
        public readonly ?int $limiteReservas,
        public readonly ?string $vehiculo,
        public readonly ?string $matricula,
        public readonly ?string $observaciones,
        public readonly ?string $estado,
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'empresa' => $this->empresa,
            'nif' => $this->nif,
            'direccion' => $this->direccion,
            'ciudad' => $this->ciudad,
            'codigo_postal' => $this->codigoPostal,
            'pais' => $this->pais,
            'telefono' => $this->telefono,
            'tipo_rol' => $this->tipoRol,
            'locale' => $this->locale,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'fecha_inicio' => $this->fechaInicio,
            'fecha_fin' => $this->fechaFin,
            'limite_reservas' => $this->limiteReservas,
            'vehiculo' => $this->vehiculo,
            'matricula' => $this->matricula,
            'observaciones' => $this->observaciones,
            'estado' => $this->estado,
        ];
    }
}
