<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Reserva;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use PDO;

final class MySqlReservaRepository implements ReservaRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function save(Reserva $reserva): Reserva
    {
        $yaEnTransaccion = $this->conn->inTransaction();
        if (!$yaEnTransaccion) {
            $this->conn->beginTransaction();
        }

        try {
            $stmt = $this->conn->prepare('
                INSERT INTO parking_reservas
                (
                    usuario_uuid, localizador, estado, estado_vehiculo,
                    fecha_reserva, entrada_prevista, salida_prevista,
                    subtotal_calculado, iva_calculado, total_calculado,
                    vehiculo, matricula, personas, tipo, vuelo, notas, canal,
                    created_at, updated_at
                ) VALUES (
                    :usuario_uuid, :localizador, :estado, :estado_vehiculo,
                    :fecha_reserva, :entrada_prevista, :salida_prevista,
                    :subtotal_calculado, :iva_calculado, :total_calculado,
                    :vehiculo, :matricula, :personas, :tipo, :vuelo, :notas, :canal,
                    :created_at, :updated_at
                )
            ');

            $stmt->execute([
                ':usuario_uuid' => $reserva->usuarioUuid()->toBytes(),
                ':localizador' => $reserva->localizador(),
                ':estado' => $reserva->estado()->value,
                ':estado_vehiculo' => $reserva->estadoVehiculo()->value,
                ':fecha_reserva' => $reserva
                    ->fechaReserva()
                    ->format('Y-m-d H:i:s'),
                ':entrada_prevista' => $reserva
                    ->entradaPrevista()
                    ->format('Y-m-d H:i:s'),
                ':salida_prevista' => $reserva
                    ->salidaPrevista()
                    ->format('Y-m-d H:i:s'),
                ':subtotal_calculado' => $reserva->subtotalCalculado(),
                ':iva_calculado' => $reserva->ivaCalculado(),
                ':total_calculado' => $reserva->totalCalculado(),
                ':vehiculo' => $reserva->vehiculo(),
                ':matricula' => $reserva->matricula(),
                ':personas' => $reserva->personas(),
                ':tipo' => $reserva->tipo()->value,
                ':vuelo' => $reserva->vuelo(),
                ':notas' => $reserva->notas(),
                ':canal' => $reserva->canal()->value,
                ':created_at' => $reserva->createdAt()?->format('Y-m-d H:i:s'),
                ':updated_at' => $reserva->updatedAt()?->format('Y-m-d H:i:s'),
            ]);

            $reservaId = (int) $this->conn->lastInsertId();

            $stmtLinea = $this->conn->prepare('
                INSERT INTO parking_reservas_servicios
                (
                    reserva_id, servicio_id, descripcion, cantidad,
                    precio_unitario, impuesto_percent,
                    total_base, total_impuesto, total_linea
                ) VALUES (
                    :reserva_id, :servicio_id, :descripcion, :cantidad,
                    :precio_unitario, :impuesto_percent,
                    :total_base, :total_impuesto, :total_linea
                )
            ');

            foreach ($reserva->lineas() as $linea) {
                $stmtLinea->execute([
                    ':reserva_id' => $reservaId,
                    ':servicio_id' => $linea->servicioId,
                    ':descripcion' => $linea->descripcion,
                    ':cantidad' => $linea->cantidad,
                    ':precio_unitario' => $linea->precioUnitario,
                    ':impuesto_percent' => $linea->impuestoPercent,
                    ':total_base' => $linea->totalBase,
                    ':total_impuesto' => $linea->totalImpuesto,
                    ':total_linea' => $linea->totalLinea,
                ]);
            }

            if (!$yaEnTransaccion) {
                $this->conn->commit();
            }

            return $reserva->conId($reservaId);
        } catch (\Throwable $e) {
            if (!$yaEnTransaccion && $this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            throw $e;
        }
    }
}
