<?php

declare(strict_types=1);

namespace App\Domain\Usuario\Entity;

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Enums\Locale;

final class Usuario
{
    private function __construct(
        private readonly UsuarioUuid $uuid,
        private readonly Email $email,
        private readonly UsuarioEstado $estado,
        private readonly Rol $rol,
        private readonly Locale $locale,
        private readonly ?string $password,
    ) {}

    public static function create(
        UsuarioUuid $uuid,
        Email $email,
        Rol $rol = Rol::Cliente,
        Locale $locale = Locale::Es,
        ?string $password = null,
    ): self {
        return new self(
            uuid: $uuid,
            email: $email,
            estado: UsuarioEstado::Pendiente,
            rol: $rol,
            locale: $locale,
            password: $password,
        );
    }

    public static function fromDatabase(
        UsuarioUuid $uuid,
        Email $email,
        UsuarioEstado $estado,
        Rol $rol,
        Locale $locale,
        ?string $password,
    ): self {
        return new self($uuid, $email, $estado, $rol, $locale, $password);
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
        );
    }
}
