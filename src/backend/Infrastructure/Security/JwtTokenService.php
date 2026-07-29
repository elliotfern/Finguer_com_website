<?php

declare(strict_types=1);

namespace App\Infrastructure\Security;

use App\Application\Usuario\Security\JwtServiceInterface;
use Firebase\JWT\JWT;

final class JwtTokenService implements JwtServiceInterface
{
    public function __construct(private readonly string $secret) {}

    public function generate(array $payload): string
    {
        return JWT::encode($payload, $this->secret, 'HS256');
    }
}
