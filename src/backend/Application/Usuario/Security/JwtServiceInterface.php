<?php

declare(strict_types=1);

namespace App\Application\Usuario\Security;

interface JwtServiceInterface
{
    public function generate(array $payload): string;
}
