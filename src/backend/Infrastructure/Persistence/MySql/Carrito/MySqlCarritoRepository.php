<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Carrito;

use App\Domain\Carrito\Entity\Carrito;
use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\Rules\ReglasReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use PDO;

final class MySqlCarritoRepository implements CarritoRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function findBySession(string $session): ?Carrito
    {
        $stmt = $this->conn->prepare('
            SELECT subtotal_sin_iva, iva_total, total_con_iva, lineas_json, hash, updated_at
            FROM carro_compra
            WHERE session = :session
            LIMIT 1
        ');
        $stmt->bindValue(':session', $session, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) {
            return null;
        }

        $snapshot = json_decode((string) $row['lineas_json'], true);
        if (!is_array($snapshot)) {
            throw new \RuntimeException(
                "Snapshot de carrito inválido para session: {$session}",
            );
        }

        $seleccionData = $snapshot['seleccion'] ?? [];
        $tz = new \DateTimeZone(ReglasReserva::TIMEZONE);

        $seleccion = new SeleccionReserva(
            tipoReserva: (string) ($seleccionData['tipoReserva'] ?? ''),
            limpiezaCodigo: (string) ($seleccionData['limpieza'] ?? '0'),
            seguroCancelacion: (bool) ($seleccionData['seguroCancelacion'] ??
                false),
            fechaEntrada: new \DateTimeImmutable(
                (string) ($seleccionData['fechaEntrada'] ?? 'now'),
                $tz,
            ),
            fechaSalida: new \DateTimeImmutable(
                (string) ($seleccionData['fechaSalida'] ?? 'now'),
                $tz,
            ),
        );

        $lineas = array_map(
            fn(array $l) => new LineaPrecio(
                codigo: (string) $l['codigo'],
                descripcion: (string) $l['descripcion'],
                cantidad: (float) $l['cantidad'],
                ivaPercent: (float) $l['iva_percent'],
                base: (float) $l['base'],
                iva: (float) $l['iva'],
                total: (float) $l['total'],
            ),
            $snapshot['lineas'] ?? [],
        );

        return Carrito::fromDatabase(
            session: $session,
            seleccion: $seleccion,
            diasReserva: (int) ($snapshot['diasReserva'] ?? 0),
            lineas: $lineas,
            subtotalSinIva: (float) $row['subtotal_sin_iva'],
            ivaTotal: (float) $row['iva_total'],
            totalConIva: (float) $row['total_con_iva'],
            hash: (string) $row['hash'],
            updatedAt: $row['updated_at']
                ? new \DateTimeImmutable((string) $row['updated_at'])
                : null,
        );
    }

    public function save(Carrito $carrito): void
    {
        $stmt = $this->conn->prepare('
            INSERT INTO carro_compra
                (session, subtotal_sin_iva, iva_total, total_con_iva, lineas_json, hash)
            VALUES
                (:session, :subtotal, :iva_total, :total, :lineas_json, :hash)
            ON DUPLICATE KEY UPDATE
                subtotal_sin_iva = VALUES(subtotal_sin_iva),
                iva_total        = VALUES(iva_total),
                total_con_iva    = VALUES(total_con_iva),
                lineas_json      = VALUES(lineas_json),
                hash             = VALUES(hash),
                updated_at       = CURRENT_TIMESTAMP
        ');

        $stmt->execute([
            ':session' => $carrito->session(),
            ':subtotal' => $carrito->subtotalSinIva(),
            ':iva_total' => $carrito->ivaTotal(),
            ':total' => $carrito->totalConIva(),
            ':lineas_json' => $carrito->lineasJson(),
            ':hash' => $carrito->hash(),
        ]);
    }
}
