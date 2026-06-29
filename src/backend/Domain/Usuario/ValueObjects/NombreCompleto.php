<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class NombreCompleto
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException(
                'El nombre no puede estar vacío.',
            );
        }

        if (strlen($value) > 255) {
            throw new \InvalidArgumentException(
                'El nombre no puede superar 255 caracteres.',
            );
        }

        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
