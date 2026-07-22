<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\ReservaConFacturaException;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\VerificadorFacturaInterface;
use DateTimeImmutable;

final class ActualizarDatosReservaUseCase
{
    public function __construct(
        private readonly ReservaRepositoryInterface $reservaRepository,
        private readonly VerificadorFacturaInterface $verificadorFactura,
    ) {}

    /**
     * @param array{
     *     id: int,
     *     estado: string,
     *     tipo: string,
     *     canal: string,
     *     entrada_prevista: string,
     *     salida_prevista: string,
     *     vehiculo: ?string,
     *     matricula: ?string,
     *     personas: ?int,
     *     vuelo: ?string,
     *     notas: ?string,
     * } $input Datos ya validados por ReservaSchema::actualizarDatos()
     */
    public function execute(array $input): Reserva
    {
        $reserva = $this->reservaRepository->findById((int) $input['id']);
        if ($reserva === null) {
            throw ReservaNotFoundException::porId((int) $input['id']);
        }

        $nuevoEstado = EstadoReserva::from($input['estado']);
        $tieneFactura = $this->verificadorFactura->existeFacturaParaReserva(
            $reserva->id(),
        );

        if ($tieneFactura && $nuevoEstado !== $reserva->estado()) {
            throw new ReservaConFacturaException($reserva->id());
        }

        $actualizada = $reserva->forzarEstado($nuevoEstado);

        $actualizada = $actualizada->actualizarDatosGenerales(
            tipo: TipoReserva::from((int) $input['tipo']),
            canal: CanalReserva::from($input['canal']),
            entradaPrevista: new DateTimeImmutable($input['entrada_prevista']),
            salidaPrevista: new DateTimeImmutable($input['salida_prevista']),
            vehiculo: $input['vehiculo'] ?? null,
            matricula: $input['matricula'] ?? null,
            personas: isset($input['personas'])
                ? (int) $input['personas']
                : null,
            vuelo: $input['vuelo'] ?? null,
            notas: $input['notas'] ?? null,
        );

        $this->reservaRepository->actualizarDatosGenerales($actualizada);

        return $actualizada;
    }
}
