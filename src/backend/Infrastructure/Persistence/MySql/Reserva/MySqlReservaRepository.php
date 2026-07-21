<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Reserva;

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\EstadoReserva;
use App\Domain\Reserva\Enums\EstadoVehiculo;
use App\Domain\Reserva\Enums\CanalReserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\Exception\EstadoConflictException;
use App\Domain\Reserva\Repository\ReservaRepositoryInterface;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use App\Domain\Shared\UsuarioUuid;
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

    public function findById(int $id): ?Reserva
    {
        $stmt = $this->conn->prepare('
        SELECT *
        FROM parking_reservas
        WHERE id = :id
        LIMIT 1
    ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $stmtLineas = $this->conn->prepare('
        SELECT *
        FROM parking_reservas_servicios
        WHERE reserva_id = :id
        ORDER BY id ASC
    ');
        $stmtLineas->bindValue(':id', $id, PDO::PARAM_INT);
        $stmtLineas->execute();
        $lineasRows = $stmtLineas->fetchAll(PDO::FETCH_ASSOC);

        $lineas = array_map(
            fn(array $l) => new ReservaServicioLinea(
                servicioId: (int) $l['servicio_id'],
                descripcion: (string) ($l['descripcion'] ?? ''),
                cantidad: (float) $l['cantidad'],
                precioUnitario: (float) $l['precio_unitario'],
                impuestoPercent: (float) $l['impuesto_percent'],
                totalBase: (float) $l['total_base'],
                totalImpuesto: (float) $l['total_impuesto'],
                totalLinea: (float) $l['total_linea'],
            ),
            $lineasRows,
        );

        return Reserva::fromDatabase(
            id: (int) $row['id'],
            usuarioUuid: UsuarioUuid::fromBytes($row['usuario_uuid']),
            localizador: (string) $row['localizador'],
            estado: EstadoReserva::from($row['estado']),
            estadoVehiculo: EstadoVehiculo::from($row['estado_vehiculo']),
            fechaReserva: new \DateTimeImmutable($row['fecha_reserva']),
            entradaPrevista: new \DateTimeImmutable($row['entrada_prevista']),
            salidaPrevista: new \DateTimeImmutable($row['salida_prevista']),
            subtotalCalculado: (float) ($row['subtotal_calculado'] ?? 0),
            ivaCalculado: (float) ($row['iva_calculado'] ?? 0),
            totalCalculado: (float) ($row['total_calculado'] ?? 0),
            vehiculo: $row['vehiculo'],
            matricula: $row['matricula'],
            personas: $row['personas'] !== null ? (int) $row['personas'] : null,
            tipo: TipoReserva::from((int) $row['tipo']),
            vuelo: $row['vuelo'],
            notas: $row['notas'],
            canal: CanalReserva::from((string) $row['canal']),
            lineas: $lineas,
            createdAt: new \DateTimeImmutable($row['created_at']),
            updatedAt: $row['updated_at'] !== null
                ? new \DateTimeImmutable($row['updated_at'])
                : null,
        );
    }

    public function actualizarEstadoVehiculo(
        int $id,
        EstadoVehiculo $anterior,
        EstadoVehiculo $nuevo,
    ): void {
        $stmt = $this->conn->prepare('
        UPDATE parking_reservas
        SET estado_vehiculo = :nuevo,
            updated_at = :updated_at
        WHERE id = :id
          AND estado_vehiculo = :anterior
    ');

        $stmt->execute([
            ':nuevo' => $nuevo->value,
            ':anterior' => $anterior->value,
            ':updated_at' => new \DateTimeImmutable(
                'now',
                new \DateTimeZone(
                    \App\Domain\Catalogo\Rules\ReglasReserva::TIMEZONE,
                ),
            )->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);

        if ($stmt->rowCount() !== 1) {
            // Alguien cambió el estado entre la lectura y la escritura.
            $actual = $this->obtenerEstadoVehiculoActual($id);

            throw new EstadoConflictException(
                id: $id,
                expected: $anterior->value,
                current: $actual,
                requested: $nuevo->value,
            );
        }
    }

    private function obtenerEstadoVehiculoActual(int $id): ?string
    {
        $stmt = $this->conn->prepare('
        SELECT estado_vehiculo FROM parking_reservas WHERE id = :id LIMIT 1
    ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $valor = $stmt->fetchColumn();
        return $valor !== false ? (string) $valor : null;
    }
    public function actualizarEstado(
        int $id,
        EstadoReserva $anterior,
        EstadoReserva $nuevo,
    ): void {
        $stmt = $this->conn->prepare('
        UPDATE parking_reservas
        SET estado = :nuevo,
            updated_at = :updated_at
        WHERE id = :id
          AND estado = :anterior
    ');

        $stmt->execute([
            ':nuevo' => $nuevo->value,
            ':anterior' => $anterior->value,
            ':updated_at' => new \DateTimeImmutable(
                'now',
                new \DateTimeZone(
                    \App\Domain\Catalogo\Rules\ReglasReserva::TIMEZONE,
                ),
            )->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);

        if ($stmt->rowCount() !== 1) {
            $actual = $this->obtenerEstadoActual($id);

            throw new EstadoConflictException(
                id: $id,
                expected: $anterior->value,
                current: $actual,
                requested: $nuevo->value,
            );
        }
    }

    private function obtenerEstadoActual(int $id): ?string
    {
        $stmt = $this->conn->prepare('
        SELECT estado FROM parking_reservas WHERE id = :id LIMIT 1
    ');
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $valor = $stmt->fetchColumn();
        return $valor !== false ? (string) $valor : null;
    }

    public function findByLocalizador(string $localizador): ?Reserva
    {
        $stmt = $this->conn->prepare('
        SELECT id FROM parking_reservas WHERE localizador = :localizador LIMIT 1
    ');
        $stmt->bindValue(':localizador', $localizador, PDO::PARAM_STR);
        $stmt->execute();

        $id = $stmt->fetchColumn();
        return $id !== false ? $this->findById((int) $id) : null;
    }

    public function actualizarDatosAnual(Reserva $reserva): void
    {
        $stmt = $this->conn->prepare('
        UPDATE parking_reservas
        SET entrada_prevista = :entrada_prevista,
            salida_prevista = :salida_prevista,
            vehiculo = :vehiculo,
            matricula = :matricula,
            vuelo = :vuelo,
            notas = :notas,
            updated_at = :updated_at
        WHERE id = :id
          AND estado = :estado
    ');

        $stmt->execute([
            ':entrada_prevista' => $reserva
                ->entradaPrevista()
                ->format('Y-m-d H:i:s'),
            ':salida_prevista' => $reserva
                ->salidaPrevista()
                ?->format('Y-m-d H:i:s'),
            ':vehiculo' => $reserva->vehiculo(),
            ':matricula' => $reserva->matricula(),
            ':vuelo' => $reserva->vuelo(),
            ':notas' => $reserva->notas(),
            ':updated_at' => $reserva->updatedAt()?->format('Y-m-d H:i:s'),
            ':id' => $reserva->id(),
            ':estado' => EstadoReserva::Anual->value,
        ]);
    }
}
