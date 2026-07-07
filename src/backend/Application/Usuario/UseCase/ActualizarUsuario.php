<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class ActualizarUsuario
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
    ) {}

    public function execute(string $uuidString, array $input): Usuario
    {
        if ($uuidString === '') {
            throw new \InvalidArgumentException('MISSING_UUID');
        }

        $uuid = UsuarioUuid::fromString($uuidString);
        $existente = $this->usuarioRepository->findByUuid($uuid);

        if ($existente === null) {
            throw new \InvalidArgumentException('NOT_FOUND');
        }

        $email = Email::fromString($input['email'] ?? '');

        $otro = $this->usuarioRepository->findByEmail($email);
        if ($otro !== null && !$otro->uuid()->equals($uuid)) {
            throw new \InvalidArgumentException('EMAIL_EXISTS');
        }

        $plainPass = trim((string) ($input['password'] ?? ''));
        $passwordHash =
            $plainPass !== ''
                ? password_hash($plainPass, PASSWORD_DEFAULT)
                : $existente->password();

        $usuarioActualizado = Usuario::fromDatabase(
            uuid: $uuid,
            email: $email,
            estado: $existente->estado(),
            rol: Rol::tryFrom($input['tipo_rol'] ?? '') ?? $existente->rol(),
            locale: Locale::tryFrom($input['locale'] ?? '') ??
                $existente->locale(),
            password: $passwordHash,
        );
        $this->usuarioRepository->save($usuarioActualizado);

        if (!empty($input['nombre'])) {
            $actualizarPerfil = new ActualizarPerfil($this->usuarioRepository);
            $actualizarPerfil->execute($uuidString, $input);
        }

        return $usuarioActualizado;
    }
}
