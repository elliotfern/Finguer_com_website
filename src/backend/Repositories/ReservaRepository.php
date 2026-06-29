<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class ReservaRepository
{
    public function __construct(private readonly PDO $conn) {}

    public function findById(int $reservaId): array
    {
        if ($reservaId <= 0) {
            return vp2_err('ID de reserva no válido.', 'RESERVA_ID_INVALID');
        }

        $stmt = $this->conn->prepare("
            SELECT
                pr.id,
                pr.localizador,
                pr.usuario_uuid,
                pr.estado,
                pr.canal,
                pr.fecha_reserva,
                pr.subtotal_calculado,
                pr.iva_calculado,
                pr.total_calculado
            FROM parking_reservas pr
            WHERE pr.id = :id
            LIMIT 1
        ");
        $stmt->bindValue(':id', $reservaId, PDO::PARAM_INT);
        $stmt->execute();
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            return vp2_err('No se encontró la reserva.', 'RESERVA_NOT_FOUND', [
                'data' => ['reserva_id' => $reservaId],
            ]);
        }

        $localizador = trim((string) ($reserva['localizador'] ?? ''));
        if ($localizador === '') {
            return vp2_err(
                'La reserva no tiene localizador.',
                'RESERVA_NO_LOCALIZADOR',
                [
                    'data' => ['reserva_id' => $reservaId],
                ],
            );
        }

        return vp2_ok('Reserva cargada correctamente.', [
            'reserva' => [
                'id' => (int) $reserva['id'],
                'localizador' => $localizador,
                'usuario_uuid' => $reserva['usuario_uuid'],
                'estado' => (string) ($reserva['estado'] ?? ''),
                'canal' => (int) ($reserva['canal'] ?? 0),
                'fecha_reserva' => (string) ($reserva['fecha_reserva'] ?? ''),
                'subtotal_calculado' => $reserva['subtotal_calculado'] ?? null,
                'iva_calculado' => $reserva['iva_calculado'] ?? null,
                'total_calculado' => $reserva['total_calculado'] ?? null,
            ],
        ]);
    }

    public function obtenerFacturaIdPorReserva(int $reservaId): ?int
    {
        $stmt = $this->conn->prepare("
        SELECT id FROM facturas
        WHERE reserva_id = :rid
        ORDER BY id ASC
        LIMIT 1
    ");
        $stmt->execute([':rid' => $reservaId]);
        $id = $stmt->fetchColumn();
        return $id ? (int) $id : null;
    }
}
