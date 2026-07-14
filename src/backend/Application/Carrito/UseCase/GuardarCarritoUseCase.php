<?php

declare(strict_types=1);

namespace App\Application\Carrito\UseCase;

use App\Application\Carrito\DTO\GuardarCarritoDTO;
use App\Application\Carrito\Exception\ReglaNegocioException;
use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use App\Domain\Catalogo\Rules\HorariosReserva;
use App\Domain\Catalogo\Rules\ReglasReserva;
use App\Domain\Catalogo\Service\CalculadoraLineasReserva;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;
use DateTimeImmutable;
use DateTimeZone;

final class GuardarCarritoUseCase
{
    public function __construct(
        private readonly CarritoRepositoryInterface $carritoRepository,
        private readonly ServicioRepositoryInterface $servicioRepository,
    ) {}

    public function execute(GuardarCarritoDTO $dto): Carrito
    {
        if ($dto->session === '') {
            throw new \InvalidArgumentException('MISSING_SESSION');
        }
        if ($dto->tipoReserva === '') {
            throw new \InvalidArgumentException('MISSING_TIPO_RESERVA');
        }
        if ($dto->fechaEntrada === '' || $dto->fechaSalida === '') {
            throw new \InvalidArgumentException('MISSING_FECHAS');
        }

        $tz = new DateTimeZone(ReglasReserva::TIMEZONE);

        try {
            $entradaDt = new DateTimeImmutable($dto->fechaEntrada, $tz);
            $salidaDt = new DateTimeImmutable($dto->fechaSalida, $tz);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('INVALID_FECHAS_FORMAT');
        }

        $rangoCheck = ReglasReserva::validarRango($entradaDt, $salidaDt);
        if (!$rangoCheck['valido']) {
            throw new ReglaNegocioException(
                $rangoCheck['codigo'],
                $rangoCheck['mensaje'],
            );
        }

        $horaEntrada = $entradaDt->format('H:i');
        $horaSalida = $salidaDt->format('H:i');

        if (
            !HorariosReserva::horaValida(
                $dto->tipoReserva,
                $entradaDt,
                $horaEntrada,
            )
        ) {
            throw new ReglaNegocioException(
                'hora_no_disponible',
                "La hora de entrada {$horaEntrada} no está disponible para el tipo de reserva seleccionado.",
            );
        }

        if (
            !HorariosReserva::horaValida(
                $dto->tipoReserva,
                $salidaDt,
                $horaSalida,
            )
        ) {
            throw new ReglaNegocioException(
                'hora_no_disponible',
                "La hora de salida {$horaSalida} no está disponible para el tipo de reserva seleccionado.",
            );
        }

        $diasReserva = ReglasReserva::calcularDias($entradaDt, $salidaDt);
        if ($diasReserva <= 0) {
            throw new \InvalidArgumentException('INVALID_DATE_RANGE');
        }

        $tarifa = $this->servicioRepository->findByCodigo(
            CodigoServicio::fromString($dto->tipoReserva),
        );
        if ($tarifa === null) {
            throw new \InvalidArgumentException(
                "TIPO_RESERVA_NO_VALIDO: {$dto->tipoReserva}",
            );
        }

        $limpieza = null;
        if ($dto->limpiezaCodigo !== '0') {
            $limpieza = $this->servicioRepository->findByCodigo(
                CodigoServicio::fromString($dto->limpiezaCodigo),
            );
            if ($limpieza === null) {
                throw new \InvalidArgumentException(
                    "LIMPIEZA_NO_VALIDA: {$dto->limpiezaCodigo}",
                );
            }
        }

        $seguro = null;
        if ($dto->seguroCancelacion) {
            $seguro = $this->servicioRepository->findByCodigo(
                CodigoServicio::fromString('SEGURO_CANCELACION'),
            );
            if ($seguro === null) {
                throw new \InvalidArgumentException('SEGURO_NO_ENCONTRADO');
            }
        }

        $lineas = CalculadoraLineasReserva::calcular(
            $tarifa,
            $diasReserva,
            $limpieza,
            $seguro,
        );

        $seleccion = new SeleccionReserva(
            tipoReserva: $dto->tipoReserva,
            limpiezaCodigo: $dto->limpiezaCodigo,
            seguroCancelacion: $dto->seguroCancelacion,
            fechaEntrada: $entradaDt,
            fechaSalida: $salidaDt,
        );

        $carrito = Carrito::crear(
            $dto->session,
            $seleccion,
            $diasReserva,
            $lineas,
        );

        $this->carritoRepository->save($carrito);

        return $carrito;
    }
}
