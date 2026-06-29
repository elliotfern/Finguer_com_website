<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class DireccionPostal
{
    private function __construct(
        private readonly ?string $direccion,
        private readonly ?string $ciudad,
        private readonly ?string $codigoPostal,
        private readonly string $pais,
    ) {}

    public static function create(
        ?string $direccion,
        ?string $ciudad,
        ?string $codigoPostal,
        ?string $pais = 'España',
    ): self {
        return new self(
            $direccion ? trim($direccion) : null,
            $ciudad ? trim($ciudad) : null,
            $codigoPostal ? trim($codigoPostal) : null,
            $pais ? trim($pais) : 'España',
        );
    }

    public function direccion(): ?string
    {
        return $this->direccion;
    }
    public function ciudad(): ?string
    {
        return $this->ciudad;
    }
    public function codigoPostal(): ?string
    {
        return $this->codigoPostal;
    }
    public function pais(): string
    {
        return $this->pais;
    }

    public function tieneDatosFacturacion(): bool
    {
        return $this->direccion !== null && $this->ciudad !== null;
    }

    public function equals(self $other): bool
    {
        return $this->direccion === $other->direccion &&
            $this->ciudad === $other->ciudad &&
            $this->codigoPostal === $other->codigoPostal &&
            $this->pais === $other->pais;
    }
}
