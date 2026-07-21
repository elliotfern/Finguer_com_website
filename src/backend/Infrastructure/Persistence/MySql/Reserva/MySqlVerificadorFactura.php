<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Reserva;

use App\Domain\Reserva\Service\VerificadorFacturaInterface;
use PDO;

final class MySqlVerificadorFactura implements VerificadorFacturaInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function existeFacturaParaReserva(int $reservaId): bool
    {
        $stmt = $this->conn->prepare('
            SELECT 1 FROM facturas WHERE reserva_id = :reserva_id LIMIT 1
        ');
        $stmt->bindValue(':reserva_id', $reservaId, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchColumn() !== false;
    }
}
