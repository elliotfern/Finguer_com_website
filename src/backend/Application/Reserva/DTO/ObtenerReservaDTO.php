<?php

declare(strict_types=1);

namespace App\Application\Reserva\DTO;

final class ObtenerReservaDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $usuarioUuid,
        public readonly string $localizador,
        public readonly string $salidaPrevista,
        public readonly string $entradaPrevista,
        public readonly ?string $matricula,
        public readonly ?string $vehiculo,
        public readonly ?string $vuelo,
        public readonly int $tipo,
        public readonly ?string $notas,
        public readonly string $canal,
        public readonly ?int $personas,
        public readonly string $fechaReserva,
        public readonly string $estado,
        public readonly string $estadoVehiculo,
        public readonly ?float $subtotalCalculado,
        public readonly ?float $ivaCalculado,
        public readonly ?float $totalCalculado,
        public readonly ?string $nombre,
        public readonly ?string $telefono,
    ) {}

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'usuario_uuid' => $this->usuarioUuid,
            'localizador' => $this->localizador,
            'salida_prevista' => $this->salidaPrevista,
            'entrada_prevista' => $this->entradaPrevista,
            'matricula' => $this->matricula,
            'vehiculo' => $this->vehiculo,
            'vuelo' => $this->vuelo,
            'tipo' => $this->tipo,
            'notas' => $this->notas,
            'canal' => $this->canal,
            'personas' => $this->personas,
            'fecha_reserva' => $this->fechaReserva,
            'estado' => $this->estado,
            'estado_vehiculo' => $this->estadoVehiculo,
            'subtotal_calculado' => $this->subtotalCalculado,
            'iva_calculado' => $this->ivaCalculado,
            'total_calculado' => $this->totalCalculado,
            'nombre' => $this->nombre,
            'telefono' => $this->telefono,
        ];
    }
}
