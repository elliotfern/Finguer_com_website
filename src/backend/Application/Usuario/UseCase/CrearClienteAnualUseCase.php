<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use PDO;

final class CrearClienteAnualUseCase
{
    public function __construct(
        private readonly PDO $conn,
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly AbonoRepositoryInterface $abonoRepository,
    ) {}

    public function execute(array $input): Usuario
    {
        $this->conn->beginTransaction();
        try {
            $buscarOCrear = new BuscarOCrearUsuario($this->usuarioRepository);
            $usuario = $buscarOCrear->execute(
                array_merge($input, ['tipo_rol' => 'cliente_anual']),
            );

            $actualizarPerfil = new ActualizarPerfil($this->usuarioRepository);
            $actualizarPerfil->execute($usuario->uuid()->toString(), $input);

            $crearAbono = new CrearAbono(
                $this->usuarioRepository,
                $this->abonoRepository,
            );
            $crearAbono->execute(
                array_merge($input, [
                    'usuario_uuid' => $usuario->uuid()->toString(),
                ]),
            );

            $this->conn->commit();

            return $usuario;
        } catch (\Throwable $e) {
            $this->conn->rollBack();
            throw $e;
        }
    }
}
