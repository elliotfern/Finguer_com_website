<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\Service\LocalizadorGeneratorInterface;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;
use DateTimeImmutable;

final class CrearReservaAnualUseCase
{
    public function __construct(
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly ReservaRepositoryInterface $reservaRepository,
        private readonly LocalizadorGeneratorInterface $localizadorGenerator,
    ) {}

    /**
     * @param array{
     *     usuario_uuid: string,
     *     diaEntrada: string,
     *     horaEntrada: string,
     *     diaSalida: ?string,
     *     horaSalida: ?string,
     *     vehiculo: ?string,
     *     matricula: ?string,
     *     vuelo: ?string,
     *     notes: ?string,
     * } $input Datos ya validados por ReservaSchema::crearAnual()
     */
    public function execute(array $input): Reserva
    {
        $usuarioUuid = UsuarioUuid::fromString($input['usuario_uuid']);
        $usuario = $this->usuarioRepository->findByUuid($usuarioUuid);

        if ($usuario === null) {
            throw new \InvalidArgumentException('USUARIO_NO_ENCONTRADO');
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

        $localizador = $this->localizadorGenerator->generar();

        $reserva = Reserva::crearAnual(
            usuarioUuid: $usuarioUuid,
            localizador: $localizador,
            entradaPrevista: $entradaDt,
            salidaPrevista: $salidaDt,
            vehiculo: $input['vehiculo'] ?? null,
            matricula: $input['matricula'] ?? null,
            vuelo: $input['vuelo'] ?? null,
            notas: $input['notes'] ?? null,
        );

        return $this->reservaRepository->save($reserva);
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
