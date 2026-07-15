<?php

declare(strict_types=1);

namespace App\Application\Reserva\UseCase;

use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Enums\UsuarioEstado;
use App\Domain\Usuario\Repository\UsuarioRepositoryInterface;

final class CrearReservaUseCase
{
    public function __construct(
        private readonly CarritoRepositoryInterface $carritoRepository,
        private readonly UsuarioRepositoryInterface $usuarioRepository,
        private readonly ServicioRepositoryInterface $servicioRepository,
        private readonly ReservaRepositoryInterface $reservaRepository,
    ) {}

    /**
     * @param array{
     *     session: string,
     *     usuario_uuid: string,
     *     localizador: string,
     *     vehiculo: string,
     *     matricula: string,
     *     vuelo: string,
     *     numeroPersonas: int,
     * } $input Datos ya validados por ReservaSchema::crear()
     */
    public function execute(array $input): Reserva
    {
        $carrito = $this->carritoRepository->findBySession($input['session']);
        if ($carrito === null) {
            throw new \InvalidArgumentException('CARRITO_NOT_FOUND');
        }

        $usuarioUuid = UsuarioUuid::fromString($input['usuario_uuid']);
        $usuario = $this->usuarioRepository->findByUuid($usuarioUuid);

        if ($usuario === null || $usuario->estado() !== UsuarioEstado::Activo) {
            throw new \InvalidArgumentException('USUARIO_NO_VALIDO');
        }

        $seleccion = $carrito->seleccion();
        $tipo = TipoReserva::fromCodigoServicio($seleccion->tipoReserva);

        $lineas = array_map(
            fn(LineaPrecio $linea) => $this->mapearLinea($linea),
            $carrito->lineas(),
        );

        $reserva = Reserva::crear(
            usuarioUuid: $usuarioUuid,
            localizador: $input['localizador'],
            entradaPrevista: $seleccion->fechaEntrada,
            salidaPrevista: $seleccion->fechaSalida,
            subtotalCalculado: $carrito->subtotalSinIva(),
            ivaCalculado: $carrito->ivaTotal(),
            totalCalculado: $carrito->totalConIva(),
            vehiculo: $input['vehiculo'],
            matricula: $input['matricula'],
            personas: (int) $input['numeroPersonas'],
            tipo: $tipo,
            vuelo: $input['vuelo'],
            lineas: $lineas,
        );

        return $this->reservaRepository->save($reserva);
    }

    private function mapearLinea(LineaPrecio $linea): ReservaServicioLinea
    {
        $servicio = $this->servicioRepository->findByCodigo(
            CodigoServicio::fromString($linea->codigo),
        );

        if ($servicio === null) {
            throw new \InvalidArgumentException(
                "SERVICIO_NO_ENCONTRADO: {$linea->codigo}",
            );
        }

        return new ReservaServicioLinea(
            servicioId: $servicio->id(),
            descripcion: $linea->descripcion,
            cantidad: $linea->cantidad,
            precioUnitario: $linea->cantidad > 0
                ? round($linea->base / $linea->cantidad, 2)
                : $linea->base,
            impuestoPercent: $linea->ivaPercent,
            totalBase: $linea->base,
            totalImpuesto: $linea->iva,
            totalLinea: $linea->total,
        );
    }
}
