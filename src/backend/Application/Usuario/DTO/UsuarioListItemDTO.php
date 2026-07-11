<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

final class UsuarioListItemDTO
{
    public function __construct(
        public readonly string $uuid,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly string $tipoRol,
        public readonly ?string $createdAt,
    ) {}

    public function toArray(): array
    {
        return [
            'uuid' => $this->uuid,
            'nombre' => $this->nombre,
            'email' => $this->email,
            'telefono' => $this->telefono,
            'tipo_rol' => $this->tipoRol,
            'createdAt' => $this->createdAt,
        ];
    }
}
