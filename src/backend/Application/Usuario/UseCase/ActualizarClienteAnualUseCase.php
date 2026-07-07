<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PDO;

final class ActualizarClienteAnualUseCase
{
    public function __construct(
        private readonly PDO $conn,
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly AbonoRepositoryInterface $abonoRepository,
    ) {}

    public function execute(array $input): Usuario
    {
        $uuidStr = trim((string) ($input['uuid'] ?? ''));
        if ($uuidStr === '') {
            throw new \InvalidArgumentException('MISSING_UUID');
        }

        $uuid = UsuarioUuid::fromString($uuidStr);
        $usuarioExistente = $this->usuarioRepository->findByUuid($uuid);
        if ($usuarioExistente === null) {
            throw new \InvalidArgumentException('NOT_FOUND');
        }

        $this->conn->beginTransaction();
        try {
            $actualizarPerfil = new ActualizarPerfil($this->usuarioRepository);
            $actualizarPerfil->execute($uuidStr, $input);

            $actualizarAbono = new ActualizarAbono(
                $this->usuarioRepository,
                $this->abonoRepository,
            );
            $actualizarAbono->execute(
                array_merge($input, ['usuario_uuid' => $uuidStr]),
            );

            $this->conn->commit();

            return $usuarioExistente;
        } catch (\Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
