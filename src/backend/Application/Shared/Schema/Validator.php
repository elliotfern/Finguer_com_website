<?php

namespace App\Application\Shared\Schema;

class Validator
{
    public static function validate(mixed $value, array $schema): array
    {
        $errors = [];

        $rules = $schema['rules'] ?? [];
        $type = $schema['type'] ?? null;

        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                $value = null;
            }
        }

        $isNullable = self::hasRule($rules, 'nullable');
        $isRequired = self::hasRule($rules, 'required');

        if ($isRequired && $value === null) {
            return ['Campo obligatorio'];
        }

        if ($value === null) {
            return $isNullable ? [] : [];
        }

        $errors = array_merge($errors, self::validateType($value, $type));

        foreach ($rules as $rule) {
            $name = $rule['name'];

            switch ($name) {
                case 'email':
                    if (
                        !is_string($value) ||
                        !filter_var($value, FILTER_VALIDATE_EMAIL)
                    ) {
                        $errors[] = 'Email inválido';
                    }
                    break;

                case 'max':
                    if (
                        is_string($value) &&
                        mb_strlen($value) > $rule['value']
                    ) {
                        $errors[] = 'Longitud máxima: ' . $rule['value'];
                    }
                    break;

                case 'min_value':
                    if (is_numeric($value) && (float) $value < $rule['value']) {
                        $errors[] = 'Valor mínimo: ' . $rule['value'];
                    }
                    break;

                case 'max_value':
                    if (is_numeric($value) && (float) $value > $rule['value']) {
                        $errors[] = 'Valor máximo: ' . $rule['value'];
                    }
                    break;

                case 'regex':
                    if (
                        !is_string($value) ||
                        !preg_match($rule['value'], $value)
                    ) {
                        $errors[] = 'Formato inválido';
                    }
                    break;

                case 'date':
                    if (!self::isValidDate($value)) {
                        $errors[] = 'Fecha inválida';
                    }
                    break;
            }
        }

        return $errors;
    }

    private static function validateType(mixed $value, ?string $type): array
    {
        return match ($type) {
            'string' => self::validateString($value),
            'int' => self::validateInt($value),
            'float' => self::validateFloat($value),
            'bool' => self::validateBool($value),
            'uuid' => self::validateUuid($value),
            default => [],
        };
    }

    private static function validateString(mixed $value): array
    {
        return is_string($value) ? [] : ['Ha de ser un string'];
    }

    private static function validateInt(mixed $value): array
    {
        return is_int($value) ? [] : ['Ha de ser un entero'];
    }

    private static function validateFloat(mixed $value): array
    {
        return is_float($value) ? [] : ['Ha de ser un número'];
    }

    private static function validateBool(mixed $value): array
    {
        return is_bool($value) ? [] : ['Ha de ser un booleano'];
    }

    private static function validateUuid(mixed $value): array
    {
        if (!is_string($value)) {
            return ['UUID inválido'];
        }

        return preg_match('/^[0-9a-f-]{36}$/', $value) ? [] : ['UUID inválido'];
    }

    private static function isValidDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return strtotime($value) !== false;
    }

    private static function hasRule(array $rules, string $name): bool
    {
        foreach ($rules as $rule) {
            if (($rule['name'] ?? null) === $name) {
                return true;
            }
        }

        return false;
    }
}
