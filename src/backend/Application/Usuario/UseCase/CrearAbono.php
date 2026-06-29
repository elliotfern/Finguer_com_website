<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Application\Usuario\Factory\UsuarioFactory;
use App\Application\Usuario\Schema\UsuarioSchema;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class CrearAbono
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly AbonoRepositoryInterface $abonoRepository,
    ) {}

    public function execute(array $input): string
    {
        // 1. Validar entrada
        $data = SchemaProcessor::process($input, UsuarioSchema::crearAbono());

        // 2. Verificar que el usuario existe
        $uuid = UsuarioUuid::fromString($data['usuario_uuid']);
        $usuario = $this->usuarioRepository->findByUuid($uuid);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        // 3. Crear y persistir abono
        $dto = CrearAbonoDTO::fromArray($data);
        $abono = UsuarioFactory::crearAbono($dto);
        $this->abonoRepository->save($abono);

        return $abono->id()->toString();
    }
}
