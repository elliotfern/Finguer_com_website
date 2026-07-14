<?php

declare(strict_types=1);

namespace App\Domain\Shared;

final class Email
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim(strtolower($value));

        if ($value === '') {
            throw new \InvalidArgumentException(
                'El campo Email no puede estar vacío.',
            );
        }

        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Email no válido: {$value}");
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
