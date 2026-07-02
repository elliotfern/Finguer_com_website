<?php

declare(strict_types=1);

namespace App\Application\Usuario\Http;

interface AuthCookieServiceInterface
{
    public function setToken(string $jwt, int $expiration): void;

    public function clear(): void;
}
