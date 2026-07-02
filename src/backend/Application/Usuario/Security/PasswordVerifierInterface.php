<?php

declare(strict_types=1);

namespace App\Application\Usuario\Security;

interface PasswordVerifierInterface
{
    public function verify(string $plainPassword, string $hashedPassword): bool;
}
