<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class Nif
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim(strtoupper($value));

        if ($value === '') {
            throw new \InvalidArgumentException('El NIF no puede estar vacío.');
        }

        if (strlen($value) > 30) {
            throw new \InvalidArgumentException(
                'El NIF no puede superar 30 caracteres.',
            );
        }

        if (!preg_match('/^[A-Z0-9\-\s]+$/', $value)) {
            throw new \InvalidArgumentException(
                "NIF con caracteres no válidos: {$value}",
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
