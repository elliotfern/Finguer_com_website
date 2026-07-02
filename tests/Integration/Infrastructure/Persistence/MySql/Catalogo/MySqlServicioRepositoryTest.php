<?php

declare(strict_types=1);

use App\Domain\Catalogo\Entity\Servicio;
use App\Domain\Catalogo\Enums\TipoServicio;
use App\Domain\Catalogo\ValueObjects\CodigoServicio;
use App\Infrastructure\Persistence\MySql\Catalogo\MySqlServicioRepository;
use PHPUnit\Framework\TestCase;

class MySqlServicioRepositoryTest extends TestCase
{
    private PDO $conn;
    private MySqlServicioRepository $repo;

    protected function setUp(): void
    {
        $dbName = $_ENV['DB_DBNAME'] ?? '';
        if (!str_contains($dbName, '_test')) {
            $this->fail(
                "PELIGRO: Solo se puede ejecutar contra BD de test. BD: {$dbName}",
            );
        }

        $this->conn = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$dbName}",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->repo = new MySqlServicioRepository($this->conn);
    }

    public function test_find_by_codigo_existente(): void
    {
        $servicio = $this->repo->findByCodigo(
            CodigoServicio::fromString('RESERVA_FINGUER'),
        );

        $this->assertNotNull($servicio);
        $this->assertInstanceOf(Servicio::class, $servicio);
        $this->assertSame('RESERVA_FINGUER', $servicio->codigo()->value());
        $this->assertSame(TipoServicio::Parking, $servicio->tipo());
    }

    public function test_find_by_codigo_inexistente(): void
    {
        $servicio = $this->repo->findByCodigo(
            CodigoServicio::fromString('CODIGO_INEXISTENTE'),
        );

        $this->assertNull($servicio);
    }

    public function test_find_all_activos(): void
    {
        $servicios = $this->repo->findAllActivos();

        $this->assertNotEmpty($servicios);
        foreach ($servicios as $servicio) {
            $this->assertInstanceOf(Servicio::class, $servicio);
            $this->assertTrue($servicio->activo());
        }
    }

    public function test_find_by_tipo_parking(): void
    {
        $servicios = $this->repo->findByTipo('parking');

        $this->assertNotEmpty($servicios);
        foreach ($servicios as $servicio) {
            $this->assertSame(TipoServicio::Parking, $servicio->tipo());
        }
    }

    public function test_precio_parking_calculado_correctamente(): void
    {
        $servicio = $this->repo->findByCodigo(
            CodigoServicio::fromString('RESERVA_FINGUER'),
        );

        $this->assertNotNull($servicio);
        $this->assertSame(100.0, $servicio->calcularPrecioParking(10));
        $this->assertSame(125.0, $servicio->calcularPrecioParking(15));
    }
}
