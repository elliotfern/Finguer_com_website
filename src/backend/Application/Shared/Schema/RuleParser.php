<?php

namespace App\Application\Shared\Schema;

class RuleParser
{
    public static function parse(array $schemaField): array
    {
        $parsed = [
            'rules' => [],
            'label' => $schemaField['label'] ?? null,
            'type' => null,
        ];

        $rulesString = $schemaField['rules'] ?? '';

        $parts = explode('|', $rulesString);

        foreach ($parts as $part) {
            // required
            if ($part === 'required') {
                $parsed['rules'][] = ['name' => 'required'];
                continue;
            }

            // nullable
            if ($part === 'nullable') {
                $parsed['rules'][] = ['name' => 'nullable'];
                continue;
            }

            // email
            if ($part === 'email') {
                $parsed['rules'][] = ['name' => 'email'];
                continue;
            }

            // max:255 (longitud de string)
            if (str_starts_with($part, 'max:')) {
                $parsed['rules'][] = [
                    'name' => 'max',
                    'value' => (int) substr($part, 4),
                ];
                continue;
            }

            // min_value:1 (valor numérico mínimo)
            if (str_starts_with($part, 'min_value:')) {
                $parsed['rules'][] = [
                    'name' => 'min_value',
                    'value' => (float) substr($part, 10),
                ];
                continue;
            }

            // max_value:20 (valor numérico máximo)
            if (str_starts_with($part, 'max_value:')) {
                $parsed['rules'][] = [
                    'name' => 'max_value',
                    'value' => (float) substr($part, 10),
                ];
                continue;
            }

            // regex:/^[a-zA-Z0-9\s]+$/
            if (str_starts_with($part, 'regex:')) {
                $parsed['rules'][] = [
                    'name' => 'regex',
                    'value' => substr($part, 6),
                ];
                continue;
            }

            // date
            if ($part === 'date') {
                $parsed['rules'][] = ['name' => 'date'];
                continue;
            }

            // type guessing
            if ($part === 'string') {
                $parsed['type'] = 'string';
            }

            if ($part === 'int') {
                $parsed['type'] = 'int';
            }

            if ($part === 'uuid') {
                $parsed['type'] = 'uuid';
            }

            if ($part === 'bool') {
                $parsed['type'] = 'bool';
            }

            if ($part === 'date') {
                $parsed['type'] = 'date';
            }
        }

        return $parsed;
    }
}
