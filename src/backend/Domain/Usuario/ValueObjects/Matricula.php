<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class Matricula
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim(strtoupper($value));

        if ($value === '') {
            throw new \InvalidArgumentException(
                'La matrícula no puede estar vacía.',
            );
        }

        if (strlen($value) > 20) {
            throw new \InvalidArgumentException(
                'La matrícula no puede superar 20 caracteres.',
            );
        }

        if (!preg_match('/^[A-Z0-9\-\s]+$/', $value)) {
            throw new \InvalidArgumentException(
                "Matrícula con caracteres no válidos: {$value}",
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
