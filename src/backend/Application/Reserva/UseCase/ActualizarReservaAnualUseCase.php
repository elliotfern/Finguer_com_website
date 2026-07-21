<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Exception\ReservaNotFoundException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use DateTimeImmutable;

final class ActualizarReservaAnualUseCase
{
    public function __construct(
        private readonly ReservaRepositoryInterface $reservaRepository,
    ) {}

    /**
     * @param array{
     *     localizador: string,
     *     diaEntrada: string,
     *     horaEntrada: string,
     *     diaSalida: ?string,
     *     horaSalida: ?string,
     *     vehiculo: ?string,
     *     matricula: ?string,
     *     vuelo: ?string,
     *     notes: ?string,
     * } $input Datos ya validados por ReservaSchema::actualizarAnual()
     */
    public function execute(array $input): Reserva
    {
        $reserva = $this->reservaRepository->findByLocalizador(
            $input['localizador'],
        );
        if ($reserva === null) {
            throw ReservaNotFoundException::porLocalizador(
                $input['localizador'],
            );
        }

        $entradaDt = $this->parsearFechaHora(
            $input['diaEntrada'],
            $input['horaEntrada'],
            'ENTRADA_INVALIDA',
        );

        $salidaDt = null;
        $diaSalida = $input['diaSalida'] ?? null;
        $horaSalida = $input['horaSalida'] ?? null;

        if (!empty($diaSalida) && !empty($horaSalida)) {
            $salidaDt = $this->parsearFechaHora(
                $diaSalida,
                $horaSalida,
                'SALIDA_INVALIDA',
            );

            if ($salidaDt <= $entradaDt) {
                throw new \InvalidArgumentException(
                    'SALIDA_ANTERIOR_A_ENTRADA',
                );
            }
        }

        $actualizada = $reserva->actualizarDatosAnual(
            entradaPrevista: $entradaDt,
            salidaPrevista: $salidaDt,
            vehiculo: $input['vehiculo'] ?? null,
            matricula: $input['matricula'] ?? null,
            vuelo: $input['vuelo'] ?? null,
            notas: $input['notes'] ?? null,
        );

        $this->reservaRepository->actualizarDatosAnual($actualizada);

        return $actualizada;
    }

    private function parsearFechaHora(
        string $dia,
        string $hora,
        string $codigoError,
    ): DateTimeImmutable {
        $dt = DateTimeImmutable::createFromFormat(
            'Y-m-d H:i:s',
            "{$dia} {$hora}:00",
        );

        if ($dt === false) {
            throw new \InvalidArgumentException($codigoError);
        }

        return $dt;
    }
}
