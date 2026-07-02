<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Usuario;

use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Enums\AbonoEstado;
use App\Domain\Usuario\Repository\AbonoRepositoryInterface;
use App\Domain\Usuario\ValueObjects\Matricula;
use PDO;

final class MySqlAbonoRepository implements AbonoRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function findById(UsuarioUuid $id): ?Abono
    {
        $stmt = $this->conn->prepare("
            SELECT id, usuario_uuid, estado, fecha_inicio, fecha_fin,
                   limite_reservas, matricula, vehiculo, observaciones
            FROM usuarios_abonos
            WHERE id = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $id->toBytes(), PDO::PARAM_LOB);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToAbono($row) : null;
    }

    public function findByUsuarioUuid(UsuarioUuid $usuarioUuid): array
    {
        $stmt = $this->conn->prepare("
            SELECT id, usuario_uuid, estado, fecha_inicio, fecha_fin,
                   limite_reservas, matricula, vehiculo, observaciones
            FROM usuarios_abonos
            WHERE usuario_uuid = :usuario_uuid
            ORDER BY fecha_inicio DESC
        ");
        $stmt->bindValue(
            ':usuario_uuid',
            $usuarioUuid->toBytes(),
            PDO::PARAM_LOB,
        );
        $stmt->execute();

        $abonos = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $abonos[] = $this->mapToAbono($row);
        }
        return $abonos;
    }

    public function findActivoByUsuarioUuid(UsuarioUuid $usuarioUuid): ?Abono
    {
        $stmt = $this->conn->prepare("
            SELECT id, usuario_uuid, estado, fecha_inicio, fecha_fin,
                   limite_reservas, matricula, vehiculo, observaciones
            FROM usuarios_abonos
            WHERE usuario_uuid = :usuario_uuid
              AND estado = 'activo'
              AND fecha_fin >= CURDATE()
            ORDER BY fecha_fin DESC
            LIMIT 1
        ");
        $stmt->bindValue(
            ':usuario_uuid',
            $usuarioUuid->toBytes(),
            PDO::PARAM_LOB,
        );
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToAbono($row) : null;
    }

    public function findActivoByMatricula(string $matricula): ?Abono
    {
        $stmt = $this->conn->prepare("
            SELECT id, usuario_uuid, estado, fecha_inicio, fecha_fin,
                   limite_reservas, matricula, vehiculo, observaciones
            FROM usuarios_abonos
            WHERE matricula = :matricula
              AND estado = 'activo'
              AND fecha_fin >= CURDATE()
            ORDER BY fecha_fin DESC
            LIMIT 1
        ");
        $stmt->bindValue(':matricula', strtoupper(trim($matricula)));
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapToAbono($row) : null;
    }

    public function save(Abono $abono): void
    {
        $stmt = $this->conn->prepare("
            INSERT INTO usuarios_abonos
                (id, usuario_uuid, estado, fecha_inicio, fecha_fin,
                 limite_reservas, matricula, vehiculo, observaciones)
            VALUES
                (:id, :usuario_uuid, :estado, :fecha_inicio, :fecha_fin,
                 :limite_reservas, :matricula, :vehiculo, :observaciones)
            ON DUPLICATE KEY UPDATE
                estado           = VALUES(estado),
                fecha_inicio     = VALUES(fecha_inicio),
                fecha_fin        = VALUES(fecha_fin),
                limite_reservas  = VALUES(limite_reservas),
                matricula        = VALUES(matricula),
                vehiculo         = VALUES(vehiculo),
                observaciones    = VALUES(observaciones)
        ");
        $stmt->execute([
            ':id' => $abono->id()->toBytes(),
            ':usuario_uuid' => $abono->usuarioUuid()->toBytes(),
            ':estado' => $abono->estado()->value,
            ':fecha_inicio' => $abono->fechaInicio()->format('Y-m-d'),
            ':fecha_fin' => $abono->fechaFin()->format('Y-m-d'),
            ':limite_reservas' => $abono->limiteReservas(),
            ':matricula' => $abono->matricula()->value(),
            ':vehiculo' => $abono->vehiculo(),
            ':observaciones' => $abono->observaciones(),
        ]);
    }

    private function mapToAbono(array $row): Abono
    {
        return Abono::fromDatabase(
            id: UsuarioUuid::fromBytes($row['id']),
            usuarioUuid: UsuarioUuid::fromBytes($row['usuario_uuid']),
            estado: AbonoEstado::from($row['estado']),
            fechaInicio: new \DateTimeImmutable($row['fecha_inicio']),
            fechaFin: new \DateTimeImmutable($row['fecha_fin']),
            limiteReservas: (int) $row['limite_reservas'],
            matricula: Matricula::fromString($row['matricula']),
            vehiculo: $row['vehiculo'],
            observaciones: $row['observaciones'],
        );
    }
}
