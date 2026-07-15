<?php

declare(strict_types=1);

use App\Domain\Reserva\Entity\Reserva;
use App\Domain\Reserva\Enums\TipoReserva;
use App\Domain\Reserva\ValueObjects\ReservaServicioLinea;
use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Infrastructure\Persistence\MySql\Reserva\MySqlReservaRepository;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use PHPUnit\Framework\TestCase;

class MySqlReservaRepositoryTest extends TestCase
{
    private PDO $conn;
    private MySqlReservaRepository $repo;
    private MySqlUsuarioRepository $usuarioRepo;
    private ?UsuarioUuid $usuarioUuidCreado = null;
    private ?int $reservaIdCreada = null;

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

        $this->repo = new MySqlReservaRepository($this->conn);
        $this->usuarioRepo = new MySqlUsuarioRepository($this->conn);
    }

    protected function tearDown(): void
    {
        if ($this->reservaIdCreada !== null) {
            $this->conn->exec(
                "DELETE FROM parking_reservas_servicios WHERE reserva_id = {$this->reservaIdCreada}",
            );
            $this->conn->exec(
                "DELETE FROM parking_reservas WHERE id = {$this->reservaIdCreada}",
            );
        }
        if ($this->usuarioUuidCreado !== null) {
            $uuidBin = $this->usuarioUuidCreado->toBytes();
            $stmt = $this->conn->prepare(
                'DELETE FROM usuarios WHERE uuid = :uuid',
            );
            $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
            $stmt->execute();
        }
    }

    private function crearUsuarioDePrueba(): UsuarioUuid
    {
        $uuid = UsuarioUuid::generate();
        $email = Email::fromString('test_' . uniqid() . '@finguer-test.com');
        $usuario = Usuario::create($uuid, $email, Rol::Cliente, Locale::Es);
        $this->usuarioRepo->save($usuario);

        $this->usuarioUuidCreado = $uuid;
        return $uuid;
    }

    public function test_guardar_reserva_asigna_id_y_persiste_datos(): void
    {
        $usuarioUuid = $this->crearUsuarioDePrueba();

        $reserva = Reserva::crear(
            usuarioUuid: $usuarioUuid,
            localizador: '0708261234',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: 'Seat Ibiza',
            matricula: '1234ABC',
            personas: 2,
            tipo: TipoReserva::FinguerClass,
            vuelo: 'IB1234',
            lineas: [
                new ReservaServicioLinea(
                    servicioId: 1,
                    descripcion: 'Reserva Finguer Class',
                    cantidad: 1.0,
                    precioUnitario: 100.0,
                    impuestoPercent: 21.0,
                    totalBase: 100.0,
                    totalImpuesto: 21.0,
                    totalLinea: 121.0,
                ),
            ],
        );

        $guardada = $this->repo->save($reserva);
        $this->reservaIdCreada = $guardada->id();

        $this->assertNotNull($guardada->id());

        $stmt = $this->conn->prepare(
            'SELECT * FROM parking_reservas WHERE id = :id',
        );
        $stmt->bindValue(':id', $guardada->id(), PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $this->assertNotFalse($row);
        $this->assertSame('0708261234', $row['localizador']);
        $this->assertSame('pendiente', $row['estado']);
        $this->assertSame('1', $row['tipo']);
        $this->assertSame('1', $row['canal']);
    }

    public function test_guardar_reserva_persiste_lineas_de_servicio(): void
    {
        $usuarioUuid = $this->crearUsuarioDePrueba();

        $reserva = Reserva::crear(
            usuarioUuid: $usuarioUuid,
            localizador: '0708269999',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 112.4,
            ivaCalculado: 23.6,
            totalCalculado: 136.0,
            vehiculo: null,
            matricula: null,
            personas: null,
            tipo: TipoReserva::FinguerClass,
            vuelo: null,
            lineas: [
                new ReservaServicioLinea(
                    1,
                    'Reserva Finguer',
                    1.0,
                    100.0,
                    21.0,
                    100.0,
                    21.0,
                    121.0,
                ),
                new ReservaServicioLinea(
                    3,
                    'Limpieza exterior',
                    1.0,
                    12.4,
                    21.0,
                    12.4,
                    2.6,
                    15.0,
                ),
            ],
        );

        $guardada = $this->repo->save($reserva);
        $this->reservaIdCreada = $guardada->id();

        $stmt = $this->conn->prepare(
            'SELECT * FROM parking_reservas_servicios WHERE reserva_id = :id ORDER BY id ASC',
        );
        $stmt->bindValue(':id', $guardada->id(), PDO::PARAM_INT);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $this->assertCount(2, $rows);
        $this->assertSame('Reserva Finguer', $rows[0]['descripcion']);
        $this->assertSame('Limpieza exterior', $rows[1]['descripcion']);
        $this->assertSame('15.00', $rows[1]['total_linea']);
    }

    public function test_guardar_reserva_hace_rollback_si_falla_una_linea(): void
    {
        $usuarioUuid = $this->crearUsuarioDePrueba();

        $reserva = Reserva::crear(
            usuarioUuid: $usuarioUuid,
            localizador: '0708268888',
            entradaPrevista: new DateTimeImmutable('2026-08-07 10:00:00'),
            salidaPrevista: new DateTimeImmutable('2026-08-11 10:00:00'),
            subtotalCalculado: 100.0,
            ivaCalculado: 21.0,
            totalCalculado: 121.0,
            vehiculo: null,
            matricula: null,
            personas: null,
            tipo: TipoReserva::FinguerClass,
            vuelo: null,
            lineas: [
                // servicio_id inexistente en la BD real rompería la FK si existiera,
                // pero aquí forzamos un error con un valor fuera de rango para tinyint UNSIGNED
                new ReservaServicioLinea(
                    99999,
                    'Servicio inválido',
                    1.0,
                    100.0,
                    21.0,
                    100.0,
                    21.0,
                    121.0,
                ),
            ],
        );

        try {
            $this->repo->save($reserva);
            $this->fail(
                'Se esperaba una excepción por servicio_id fuera de rango',
            );
        } catch (\Throwable $e) {
            // esperado
        }

        // Verificamos que NO quedó ninguna reserva huérfana persistida
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM parking_reservas WHERE localizador = :loc',
        );
        $stmt->bindValue(':loc', '0708268888');
        $stmt->execute();
        $this->assertSame(0, (int) $stmt->fetchColumn());
    }
}
