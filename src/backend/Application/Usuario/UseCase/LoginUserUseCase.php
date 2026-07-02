<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Domain\Shared\Email;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Application\Shared\Exception\AuthException;
use App\Application\Usuario\Http\AuthCookieServiceInterface;
use App\Application\Usuario\Security\JwtServiceInterface;
use App\Application\Usuario\Security\PasswordVerifierInterface;

final class LoginUserUseCase
{
    public function __construct(
        private UsuarioRepositoryInterface $repository,
        private PasswordVerifierInterface $passwordVerifier,
        private JwtServiceInterface $jwtService,
        private AuthCookieServiceInterface $cookieService,
    ) {}

    public function execute(string $email, string $password): array
    {
        $emailVO = Email::fromString($email);

        $user = $this->repository->findByEmail($emailVO);

        if ($user === null) {
            throw new AuthException('Credencials invàlides.');
        }

        if (!$this->passwordVerifier->verify($password, $user->password())) {
            throw new AuthException('Credencials invàlides.');
        }

        if (!($user->esAdmin() || $user->esTrabajador())) {
            throw new AuthException('Credencials invàlides.');
        }

        $token = $this->jwtService->generate([
            'sub' => $user->uuid()->toString(),
            'role' => $user->rol()->value,
        ]);

        $expiration = time() + 10 * 24 * 60 * 60;

        $this->cookieService->setToken($token, $expiration);

        return [
            'status' => 'success',
            'message' => 'Accés autoritzat, accedint a la intranet...',
        ];
    }
}
