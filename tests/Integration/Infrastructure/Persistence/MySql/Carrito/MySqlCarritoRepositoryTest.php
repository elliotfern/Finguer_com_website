<?php

declare(strict_types=1);

use App\Domain\Carrito\ValueObjects\SeleccionReserva;
use App\Domain\Catalogo\Rules\ReglasReserva;
use App\Domain\Catalogo\ValueObjects\LineaPrecio;
use App\Domain\Carrito\Entity\Carrito;
use App\Infrastructure\Persistence\MySql\Carrito\MySqlCarritoRepository;
use PHPUnit\Framework\TestCase;

class MySqlCarritoRepositoryTest extends TestCase
{
    private PDO $conn;
    private MySqlCarritoRepository $repo;

    protected function setUp(): void
    {
        $dbName = $_ENV['DB_DBNAME'] ?? '';
        if (!str_contains($dbName, '_test')) {
            $this->fail(
                'PELIGRO: Los tests de integración solo pueden ejecutarse contra una BD de test. ' .
                    "BD detectada: {$dbName}",
            );
        }

        $this->conn = new PDO(
            "mysql:host={$_ENV['DB_HOST']};dbname={$_ENV['DB_DBNAME']}",
            $_ENV['DB_USER'],
            $_ENV['DB_PASS'],
        );
        $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->repo = new MySqlCarritoRepository($this->conn);
    }

    protected function tearDown(): void
    {
        $this->conn->exec(
            "DELETE FROM carro_compra WHERE session LIKE 'test_session_%'",
        );
    }

    private function makeSeleccion(): SeleccionReserva
    {
        $tz = new DateTimeZone(ReglasReserva::TIMEZONE);

        return new SeleccionReserva(
            tipoReserva: 'RESERVA_FINGUER',
            limpiezaCodigo: '0',
            seguroCancelacion: false,
            fechaEntrada: new DateTimeImmutable('2026-08-01 10:00:00', $tz),
            fechaSalida: new DateTimeImmutable('2026-08-05 10:00:00', $tz),
        );
    }

    /**
     * @return LineaPrecio[]
     */
    private function makeLineas(): array
    {
        return [
            new LineaPrecio(
                'RESERVA_FINGUER',
                'Reserva Finguer',
                1.0,
                21.0,
                100.0,
                21.0,
                121.0,
            ),
        ];
    }

    public function test_guardar_y_recuperar_carrito_por_session(): void
    {
        $session = 'test_session_' . uniqid();
        $carrito = Carrito::crear(
            $session,
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->repo->save($carrito);

        $recuperado = $this->repo->findBySession($session);

        $this->assertNotNull($recuperado);
        $this->assertSame($session, $recuperado->session());
        $this->assertSame(4, $recuperado->diasReserva());
        $this->assertSame(100.0, $recuperado->subtotalSinIva());
        $this->assertSame(21.0, $recuperado->ivaTotal());
        $this->assertSame(121.0, $recuperado->totalConIva());
        $this->assertSame($carrito->hash(), $recuperado->hash());
    }

    public function test_recupera_seleccion_correctamente(): void
    {
        $session = 'test_session_' . uniqid();
        $carrito = Carrito::crear(
            $session,
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->repo->save($carrito);
        $recuperado = $this->repo->findBySession($session);

        $this->assertSame(
            'RESERVA_FINGUER',
            $recuperado->seleccion()->tipoReserva,
        );
        $this->assertFalse($recuperado->seleccion()->tieneLimpieza());
        $this->assertSame(
            '2026-08-01 10:00:00',
            $recuperado->seleccion()->fechaEntrada->format('Y-m-d H:i:s'),
        );
    }

    public function test_recupera_lineas_correctamente(): void
    {
        $session = 'test_session_' . uniqid();
        $carrito = Carrito::crear(
            $session,
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );

        $this->repo->save($carrito);
        $recuperado = $this->repo->findBySession($session);

        $this->assertCount(1, $recuperado->lineas());
        $this->assertSame('RESERVA_FINGUER', $recuperado->lineas()[0]->codigo);
        $this->assertSame(121.0, $recuperado->lineas()[0]->total);
    }

    public function test_guardar_dos_veces_actualiza_la_misma_session(): void
    {
        $session = 'test_session_' . uniqid();
        $carritoInicial = Carrito::crear(
            $session,
            $this->makeSeleccion(),
            4,
            $this->makeLineas(),
        );
        $this->repo->save($carritoInicial);

        $lineasNuevas = [
            new LineaPrecio(
                'RESERVA_FINGUER_GOLD',
                'Reserva Gold',
                1.0,
                21.0,
                140.0,
                29.4,
                169.4,
            ),
        ];
        $carritoActualizado = Carrito::crear(
            $session,
            $this->makeSeleccion(),
            4,
            $lineasNuevas,
        );
        $this->repo->save($carritoActualizado);

        $recuperado = $this->repo->findBySession($session);

        $this->assertSame(169.4, $recuperado->totalConIva());
        $this->assertCount(1, $recuperado->lineas());
        $this->assertSame(
            'RESERVA_FINGUER_GOLD',
            $recuperado->lineas()[0]->codigo,
        );
    }

    public function test_buscar_session_inexistente_devuelve_null(): void
    {
        $resultado = $this->repo->findBySession(
            'sesion-que-no-existe-' . uniqid(),
        );
        $this->assertNull($resultado);
    }
}
