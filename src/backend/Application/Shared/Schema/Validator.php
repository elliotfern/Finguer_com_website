<?php

namespace App\Application\Shared\Schema;

class Validator
{
    public static function validate(mixed $value, array $schema): array
    {
        $errors = [];

        $rules = $schema['rules'] ?? [];
        $type = $schema['type'] ?? null;

        /**
         * 🔥 HARD NORMALIZATION (critical)
         */
        if (is_string($value)) {
            $value = trim($value);

            if ($value === '') {
                $value = null;
            }
        }

        $isNullable = self::hasRule($rules, 'nullable');
        $isRequired = self::hasRule($rules, 'required');

        /**
         * REQUIRED CHECK (FIRST LINE OF DEFENSE)
         */
        if ($isRequired && $value === null) {
            return ['Campo obligatorio'];
        }

        /**
         * NULL HANDLING
         */
        if ($value === null) {
            return $isNullable ? [] : [];
        }

        /**
         * TYPE VALIDATION
         */
        $errors = array_merge($errors, self::validateType($value, $type));

        /**
         * RULE VALIDATION
         */
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

                case 'date':
                    if (!self::isValidDate($value)) {
                        $errors[] = 'Fecha inválida';
                    }
                    break;
            }
        }

        return $errors;
    }

    /**
     * TYPE VALIDATION
     */
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
        return is_string($value) ? [] : ['Tiene que ser texto'];
    }

    private static function validateInt(mixed $value): array
    {
        return is_int($value) ? [] : ['Tiene que ser un número entero'];
    }

    private static function validateFloat(mixed $value): array
    {
        return is_float($value) ? [] : ['Tiene que ser un texto'];
    }

    private static function validateBool(mixed $value): array
    {
        return is_bool($value) ? [] : ['Tiene que ser un booleano'];
    }

    private static function validateUuid(mixed $value): array
    {
        if (!is_string($value)) {
            return ['UUID invàlid'];
        }

        return preg_match('/^[0-9a-f-]{36}$/', $value) ? [] : ['UUID inválido'];
    }

    /**
     * DATE
     */
    private static function isValidDate(mixed $value): bool
    {
        if (!is_string($value)) {
            return false;
        }

        return strtotime($value) !== false;
    }

    /**
     * HELPERS
     */
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
