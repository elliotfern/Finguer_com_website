<?php

namespace App\Application\Shared\Schema;

class Normalizer
{
    public static function normalize(mixed $value, array $rules): mixed
    {
        $type = $rules['type'] ?? null;

        /*
         * Empty string -> null
         */
        if ($value === '') {
            $value = null;
        }

        /*
         * Null stays null
         */
        if ($value === null) {
            return null;
        }

        return match ($type) {
            'string' => self::normalizeString($value, $rules),

            'int' => self::normalizeInt($value),

            'float' => self::normalizeFloat($value),

            'bool' => self::normalizeBool($value),

            'uuid' => self::normalizeUuid($value),

            default => $value,
        };
    }

    private static function normalizeString(mixed $value, array $rules): ?string
    {
        $value = (string) $value;

        if (($rules['trim'] ?? true) === true) {
            $value = trim($value);
        }

        return $value;
    }

    private static function normalizeInt(mixed $value): ?int
    {
        return (int) $value;
    }

    private static function normalizeFloat(mixed $value): ?float
    {
        return (float) $value;
    }

    private static function normalizeBool(mixed $value): ?bool
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_BOOLEAN,
            FILTER_NULL_ON_FAILURE,
        );
    }

    private static function normalizeUuid(mixed $value): ?string
    {
        return strtolower(trim((string) $value));
    }
}
