<?php

declare(strict_types=1);

namespace App\Application\Usuario\UseCase;

use App\Application\Shared\Schema\SchemaProcessor;
use App\Application\Usuario\DTO\CrearAbonoDTO;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Application\Usuario\Schema\UsuarioSchema;

final class ActualizarAbono
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly AbonoRepositoryInterface $abonoRepository,
    ) {}

    public function execute(array $input): string
    {
        // 1. Validar entrada (mismo schema que crear: misma forma de datos)
        $data = SchemaProcessor::process($input, UsuarioSchema::crearAbono());

        // 2. Verificar que el usuario existe
        $uuid = UsuarioUuid::fromString($data['usuario_uuid']);
        $usuario = $this->usuarioRepository->findByUuid($uuid);

        if ($usuario === null) {
            throw new \RuntimeException('Usuario no encontrado.');
        }

        // 3. Buscar abono existente del usuario (upsert: actualiza si existe, crea si no)
        $abonoExistente =
            $this->abonoRepository->findByUsuarioUuid($uuid)[0] ?? null;

        $dto = CrearAbonoDTO::fromArray($data);

        $abono = Abono::fromDatabase(
            id: $abonoExistente?->id() ?? UsuarioUuid::generate(),
            usuarioUuid: $uuid,
            estado: AbonoEstado::tryFrom($input['estado'] ?? '') ??
                ($abonoExistente?->estado() ?? AbonoEstado::Activo),
            fechaInicio: new \DateTimeImmutable($dto->fechaInicio),
            fechaFin: new \DateTimeImmutable($dto->fechaFin),
            limiteReservas: $dto->limiteReservas,
            matricula: Matricula::fromString($dto->matricula),
            vehiculo: $dto->vehiculo,
            observaciones: $dto->observaciones,
        );

        $this->abonoRepository->save($abono);

        return $abono->id()->toString();
    }
}
