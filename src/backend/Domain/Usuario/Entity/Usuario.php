<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Entity;

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Enums\Locale;
use DateTimeImmutable;

final class Usuario
{
    private function __construct(
        private readonly UsuarioUuid $uuid,
        private readonly Email $email,
        private readonly UsuarioEstado $estado,
        private readonly Rol $rol,
        private readonly Locale $locale,
        private readonly ?string $password,
        private readonly ?DateTimeImmutable $createdAt = null,
        private readonly ?DateTimeImmutable $updatedAt = null,
    ) {}

    public static function create(
        UsuarioUuid $uuid,
        Email $email,
        Rol $rol = Rol::Cliente,
        Locale $locale = Locale::Es,
        ?string $password = null,
    ): self {
        $now = new DateTimeImmutable();

        return new self(
            uuid: $uuid,
            email: $email,
            estado: UsuarioEstado::Activo,
            rol: $rol,
            locale: $locale,
            password: $password,
            createdAt: $now,
            updatedAt: $now,
        );
    }

    public static function fromDatabase(
        UsuarioUuid $uuid,
        Email $email,
        UsuarioEstado $estado,
        Rol $rol,
        Locale $locale,
        ?string $password,
        ?DateTimeImmutable $createdAt = null,
        ?DateTimeImmutable $updatedAt = null,
    ): self {
        return new self(
            $uuid,
            $email,
            $estado,
            $rol,
            $locale,
            $password,
            $createdAt,
            $updatedAt,
        );
    }

    public function uuid(): UsuarioUuid
    {
        return $this->uuid;
    }
    public function email(): Email
    {
        return $this->email;
    }
    public function estado(): UsuarioEstado
    {
        return $this->estado;
    }
    public function rol(): Rol
    {
        return $this->rol;
    }
    public function locale(): Locale
    {
        return $this->locale;
    }
    public function hasPassword(): bool
    {
        return $this->password !== null;
    }

    public function passwordHash(): ?string
    {
        return $this->password;
    }

    public function password(): ?string
    {
        return $this->password;
    }

    public function createdAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function esAdmin(): bool
    {
        return $this->rol === Rol::Admin;
    }

    public function esTrabajador(): bool
    {
        return $this->rol === Rol::Trabajador || $this->rol === Rol::Admin;
    }

    public function esCliente(): bool
    {
        return $this->rol === Rol::Cliente || $this->rol === Rol::ClienteAnual;
    }

    public function bloquear(): self
    {
        return new self(
            $this->uuid,
            $this->email,
            UsuarioEstado::Bloqueado,
            $this->rol,
            $this->locale,
            $this->password,
            $this->createdAt,
            new DateTimeImmutable(), // updatedAt se refresca
        );
    }
}
