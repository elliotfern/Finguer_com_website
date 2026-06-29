<?php

declare(strict_types=1);

namespace App\Application\Usuario\DTO;

use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;

final class CrearUsuarioDTO
{
    public function __construct(
        public readonly string $email,
        public readonly Rol $rol = Rol::Cliente,
        public readonly Locale $locale = Locale::Es,
        public readonly ?string $password = null,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            email: $data['email'],
            rol: Rol::tryFrom($data['tipo_rol'] ?? '') ?? Rol::Cliente,
            locale: Locale::tryFrom($data['locale'] ?? '') ?? Locale::Es,
            password: $data['password'] ?? null,
        );
    }
}
