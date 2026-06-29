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

            // max:255
            if (str_starts_with($part, 'max:')) {
                $parsed['rules'][] = [
                    'name' => 'max',
                    'value' => (int) substr($part, 4),
                ];
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
