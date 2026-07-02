<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\ValueObjects;

final class CodigoServicio
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim(strtoupper($value));

        if ($value === '') {
            throw new \InvalidArgumentException(
                'El código de servicio no puede estar vacío.',
            );
        }

        if (strlen($value) > 50) {
            throw new \InvalidArgumentException(
                'El código de servicio no puede superar 50 caracteres.',
            );
        }

        if (!preg_match('/^[A-Z0-9_]+$/', $value)) {
            throw new \InvalidArgumentException(
                "Código de servicio no válido: {$value}",
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
