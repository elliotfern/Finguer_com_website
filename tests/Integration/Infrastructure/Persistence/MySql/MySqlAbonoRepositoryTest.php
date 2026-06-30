<?php

declare(strict_types=1);

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Abono;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\ValueObjects\Matricula;
use App\Infrastructure\Persistence\MySql\MySqlAbonoRepository;
use App\Infrastructure\Persistence\MySql\MySqlUsuarioRepository;
use PHPUnit\Framework\TestCase;

class MySqlAbonoRepositoryTest extends TestCase
{
    private PDO $conn;
    private MySqlAbonoRepository $abonoRepo;
    private MySqlUsuarioRepository $usuarioRepo;
    private UsuarioUuid $usuarioUuid;

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

        $this->abonoRepo = new MySqlAbonoRepository($this->conn);
        $this->usuarioRepo = new MySqlUsuarioRepository($this->conn);

        // Crear usuario base para los tests de abono
        $this->usuarioUuid = UsuarioUuid::generate();
        $email = Email::fromString(
            'test_abono_' . uniqid() . '@finguer-test.com',
        );
        $usuario = Usuario::create(
            $this->usuarioUuid,
            $email,
            Rol::ClienteAnual,
            Locale::Es,
        );
        $this->usuarioRepo->save($usuario);
    }

    protected function tearDown(): void
    {
        $this->conn->exec(
            'DELETE FROM usuarios_abonos WHERE usuario_uuid = ' .
                $this->conn->quote($this->usuarioUuid->toBytes()),
        );
        $this->conn->exec(
            'DELETE FROM usuarios WHERE uuid = ' .
                $this->conn->quote($this->usuarioUuid->toBytes()),
        );
    }

    public function test_guardar_y_recuperar_abono(): void
    {
        $abono = Abono::create(
            UsuarioUuid::generate(),
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            Matricula::fromString('1234ABC'),
        );

        $this->abonoRepo->save($abono);

        $recuperado = $this->abonoRepo->findById($abono->id());

        $this->assertNotNull($recuperado);
        $this->assertSame('1234ABC', $recuperado->matricula()->value());
    }

    public function test_find_activo_by_usuario_uuid(): void
    {
        $abono = Abono::create(
            UsuarioUuid::generate(),
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            Matricula::fromString('1234ABC'),
        );
        $this->abonoRepo->save($abono);

        $activo = $this->abonoRepo->findActivoByUsuarioUuid($this->usuarioUuid);

        $this->assertNotNull($activo);
        $this->assertTrue($abono->id()->equals($activo->id()));
    }

    public function test_find_activo_by_matricula(): void
    {
        $matricula = strtoupper('TEST' . substr(uniqid(), -4));
        $abono = Abono::create(
            UsuarioUuid::generate(),
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            Matricula::fromString($matricula),
        );
        $this->abonoRepo->save($abono);

        $encontrado = $this->abonoRepo->findActivoByMatricula($matricula);

        $this->assertNotNull($encontrado);
        $this->assertSame($matricula, $encontrado->matricula()->value());
    }

    public function test_find_by_usuario_uuid_devuelve_array(): void
    {
        $abono = Abono::create(
            UsuarioUuid::generate(),
            $this->usuarioUuid,
            new \DateTimeImmutable('2026-01-01'),
            new \DateTimeImmutable('2026-12-31'),
            Matricula::fromString('1234ABC'),
        );
        $this->abonoRepo->save($abono);

        $abonos = $this->abonoRepo->findByUsuarioUuid($this->usuarioUuid);

        $this->assertCount(1, $abonos);
    }
}
