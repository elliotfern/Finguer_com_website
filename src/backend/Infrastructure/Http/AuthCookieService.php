<?php

declare(strict_types=1);

namespace App\Infrastructure\Http;

use App\Application\Usuario\Http\AuthCookieServiceInterface;

final class AuthCookieService implements AuthCookieServiceInterface
{
    public function setToken(string $jwt, int $expiration): void
    {
        $isProd = str_contains($_SERVER['HTTP_HOST'] ?? '', 'finguer.com');

        $cookieOptions = [
            'expires' => $expiration,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Lax',
            'secure' => $isProd,
        ];

        if ($isProd) {
            $cookieOptions['domain'] = '.finguer.com';
        }

        setcookie('token', $jwt, $cookieOptions);
    }

    public function clear(): void
    {
        setcookie('token', '', time() - 3600, '/');
    }
}
