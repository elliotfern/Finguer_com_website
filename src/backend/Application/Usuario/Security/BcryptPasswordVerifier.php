<?php

declare(strict_types=1);

namespace App\Application\Usuario\Security;

final class BcryptPasswordVerifier implements PasswordVerifierInterface
{
    public function verify(string $plainPassword, string $hashedPassword): bool
    {
        return password_verify($plainPassword, $hashedPassword);
    }
}
