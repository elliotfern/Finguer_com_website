<?php

declare(strict_types=1);

namespace App\Domain\Usuario\ValueObjects;

final class Telefono
{
    private string $value;

    private function __construct(string $value)
    {
        $value = trim($value);

        if ($value === '') {
            throw new \InvalidArgumentException(
                'El teléfono no puede estar vacío.',
            );
        }

        $telefonos = preg_split('/\s*\/\/\s*/', $value);

        if ($telefonos === false || $telefonos === []) {
            throw new \InvalidArgumentException("Teléfono no válido: {$value}");
        }

        foreach ($telefonos as $telefono) {
            $telefono = trim($telefono);

            if (!preg_match('/^\+?[0-9\s\-().]{6,30}$/', $telefono)) {
                throw new \InvalidArgumentException(
                    "Teléfono no válido: {$value}",
                );
            }

            $digits = preg_replace('/\D/', '', $telefono);

            if ($digits === null || strlen($digits) < 6) {
                throw new \InvalidArgumentException(
                    "Teléfono no válido: {$value}",
                );
            }
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
