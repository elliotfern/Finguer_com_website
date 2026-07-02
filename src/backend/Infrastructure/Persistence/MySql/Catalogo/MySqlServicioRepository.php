<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Catalogo;

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Repository\ServicioRepositoryInterface;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;
use PDO;

final class MySqlServicioRepository implements ServicioRepositoryInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function findByCodigo(CodigoServicio $codigo): ?Servicio
    {
        $stmt = $this->conn->prepare("
            SELECT id, codigo, nombre, descripcion, tipo, iva_percent,
                   precio_base, dias_incluidos, min_con_iva, extra_dia_con_iva,
                   modo_precio, seg_umbral_con_iva, seg_min_con_iva, seg_factor, activo
            FROM parking_servicios_catalogo
            WHERE codigo = :codigo AND activo = 1
            LIMIT 1
        ");
        $stmt->execute([':codigo' => $codigo->value()]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? Servicio::fromDatabase($row) : null;
    }

    public function findAllActivos(): array
    {
        $stmt = $this->conn->prepare("
            SELECT id, codigo, nombre, descripcion, tipo, iva_percent,
                   precio_base, dias_incluidos, min_con_iva, extra_dia_con_iva,
                   modo_precio, seg_umbral_con_iva, seg_min_con_iva, seg_factor, activo
            FROM parking_servicios_catalogo
            WHERE activo = 1
            ORDER BY id ASC
        ");
        $stmt->execute();

        $servicios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $servicios[] = Servicio::fromDatabase($row);
        }
        return $servicios;
    }

    public function findByTipo(string $tipo): array
    {
        $stmt = $this->conn->prepare("
            SELECT id, codigo, nombre, descripcion, tipo, iva_percent,
                   precio_base, dias_incluidos, min_con_iva, extra_dia_con_iva,
                   modo_precio, seg_umbral_con_iva, seg_min_con_iva, seg_factor, activo
            FROM parking_servicios_catalogo
            WHERE tipo = :tipo AND activo = 1
            ORDER BY id ASC
        ");
        $stmt->execute([':tipo' => $tipo]);

        $servicios = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $servicios[] = Servicio::fromDatabase($row);
        }
        return $servicios;
    }
}
