<?php
// tests/Support/JwtTestTokenFactory.php

declare(strict_types=1);

namespace Tests\Support;

use Firebase\JWT\JWT;

final class JwtTestTokenFactory
{
    public static function generar(string $uuid, string $role = 'admin'): string
    {
        $secret = $_ENV['TOKEN'] ?? '';
        if ($secret === '') {
            throw new \RuntimeException('Falta TOKEN en el entorno de test');
        }

        return JWT::encode(
            [
                'sub' => $uuid,
                'role' => $role,
                'exp' => time() + 3600,
            ],
            $secret,
            'HS256',
        );
    }
}
