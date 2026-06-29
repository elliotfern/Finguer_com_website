<?php

declare(strict_types=1);

namespace App\Domain\Shared;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UsuarioUuid
{
    private function __construct(private readonly UuidInterface $uuid) {}

    public static function generate(): self
    {
        return new self(Uuid::uuid7());
    }

    public static function fromString(string $value): self
    {
        if (!Uuid::isValid($value)) {
            throw new \InvalidArgumentException("UUID no válido: {$value}");
        }
        return new self(Uuid::fromString($value));
    }

    public static function fromBytes(string $bytes): self
    {
        if (strlen($bytes) !== 16) {
            throw new \InvalidArgumentException('UUID debe ser BINARY(16).');
        }
        return new self(Uuid::fromBytes($bytes));
    }

    public function toBytes(): string
    {
        return $this->uuid->getBytes();
    }

    public function toString(): string
    {
        return $this->uuid->toString();
    }

    public function equals(self $other): bool
    {
        return $this->uuid->equals($other->uuid);
    }

    public function __toString(): string
    {
        return $this->toString();
    }
}
