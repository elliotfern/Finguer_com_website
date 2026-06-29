<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Entity;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\Nif;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Domain\Usuario\ValueObjects\Telefono;

final class Perfil
{
    private function __construct(
        private readonly UsuarioUuid $usuarioUuid,
        private readonly NombreCompleto $nombre,
        private readonly ?Telefono $telefono,
        private readonly ?Nif $nif,
        private readonly ?string $empresa,
        private readonly DireccionPostal $direccion,
    ) {}

    public static function create(
        UsuarioUuid $usuarioUuid,
        NombreCompleto $nombre,
        ?Telefono $telefono = null,
        ?Nif $nif = null,
        ?string $empresa = null,
        ?DireccionPostal $direccion = null,
    ): self {
        return new self(
            $usuarioUuid,
            $nombre,
            $telefono,
            $nif,
            $empresa,
            $direccion ?? DireccionPostal::create(null, null, null),
        );
    }

    public static function fromDatabase(
        UsuarioUuid $usuarioUuid,
        NombreCompleto $nombre,
        ?Telefono $telefono,
        ?Nif $nif,
        ?string $empresa,
        DireccionPostal $direccion,
    ): self {
        return new self(
            $usuarioUuid,
            $nombre,
            $telefono,
            $nif,
            $empresa,
            $direccion,
        );
    }

    public function usuarioUuid(): UsuarioUuid
    {
        return $this->usuarioUuid;
    }
    public function nombre(): NombreCompleto
    {
        return $this->nombre;
    }
    public function telefono(): ?Telefono
    {
        return $this->telefono;
    }
    public function nif(): ?Nif
    {
        return $this->nif;
    }
    public function empresa(): ?string
    {
        return $this->empresa;
    }
    public function direccion(): DireccionPostal
    {
        return $this->direccion;
    }

    public function tieneDatosFacturacion(): bool
    {
        return $this->direccion->tieneDatosFacturacion() && $this->nif !== null;
    }
}
