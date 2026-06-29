<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

final class ActualizarPerfilDTO
{
    public function __construct(
        public readonly string $nombre,
        public readonly ?string $telefono = null,
        public readonly ?string $empresa = null,
        public readonly ?string $nif = null,
        public readonly ?string $direccion = null,
        public readonly ?string $ciudad = null,
        public readonly ?string $codigoPostal = null,
        public readonly ?string $pais = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            nombre: $data['nombre'],
            telefono: $data['telefono'] ?? null,
            empresa: $data['empresa'] ?? null,
            nif: $data['nif'] ?? null,
            direccion: $data['direccion'] ?? null,
            ciudad: $data['ciudad'] ?? null,
            codigoPostal: $data['codigo_postal'] ?? null,
            pais: $data['pais'] ?? null,
        );
    }
}
