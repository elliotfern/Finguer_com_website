<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\Rol;

final class UsuarioResumen
{
    public function __construct(
        public readonly UsuarioUuid $uuid,
        public readonly string $nombre,
        public readonly string $email,
        public readonly string $telefono,
        public readonly Rol $rol,
        public readonly ?\DateTimeImmutable $createdAt,
    ) {}
}
