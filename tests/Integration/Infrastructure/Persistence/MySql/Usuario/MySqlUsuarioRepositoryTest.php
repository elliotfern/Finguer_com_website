<?php

declare(strict_types=1);

use App\Domain\Shared\Email;
use App\Domain\Shared\UsuarioUuid;
use App\Domain\Usuario\Entity\Perfil;
use App\Domain\Usuario\Entity\Usuario;
use App\Domain\Usuario\Enums\Locale;
use App\Domain\Usuario\Enums\Rol;
use App\Domain\Usuario\ValueObjects\DireccionPostal;
use App\Domain\Usuario\ValueObjects\NombreCompleto;
use App\Infrastructure\Persistence\MySql\Usuario\MySqlUsuarioRepository;
use PHPUnit\Framework\TestCase;

class MySqlUsuarioRepositoryTest extends TestCase
{
    private PDO $conn;
    private MySqlUsuarioRepository $repo;

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
        $this->repo = new MySqlUsuarioRepository($this->conn);
    }

    protected function tearDown(): void
    {
        // Limpieza: eliminar usuarios de test creados
        $this->conn->exec(
            "DELETE FROM usuarios_perfil WHERE usuario_uuid IN (SELECT uuid FROM usuarios WHERE email LIKE 'test_%@finguer-test.com')",
        );
        $this->conn->exec(
            "DELETE FROM usuarios WHERE email LIKE 'test_%@finguer-test.com'",
        );
    }

    public function test_guardar_y_recuperar_usuario_por_uuid(): void
    {
        $uuid = UsuarioUuid::generate();
        $email = Email::fromString('test_' . uniqid() . '@finguer-test.com');
        $usuario = Usuario::create($uuid, $email, Rol::Cliente, Locale::Es);

        $this->repo->save($usuario);

        $recuperado = $this->repo->findByUuid($uuid);

        $this->assertNotNull($recuperado);
        $this->assertSame($email->value(), $recuperado->email()->value());
        $this->assertSame(Rol::Cliente, $recuperado->rol());
    }

    public function test_buscar_por_email(): void
    {
        $uuid = UsuarioUuid::generate();
        $email = Email::fromString('test_' . uniqid() . '@finguer-test.com');
        $usuario = Usuario::create($uuid, $email, Rol::Cliente, Locale::Es);

        $this->repo->save($usuario);

        $recuperado = $this->repo->findByEmail($email);

        $this->assertNotNull($recuperado);
        $this->assertTrue($uuid->equals($recuperado->uuid()));
    }

    public function test_buscar_uuid_inexistente_devuelve_null(): void
    {
        $resultado = $this->repo->findByUuid(UsuarioUuid::generate());
        $this->assertNull($resultado);
    }

    public function test_guardar_y_recuperar_perfil(): void
    {
        $uuid = UsuarioUuid::generate();
        $email = Email::fromString('test_' . uniqid() . '@finguer-test.com');
        $usuario = Usuario::create($uuid, $email, Rol::Cliente, Locale::Es);
        $this->repo->save($usuario);

        $perfil = Perfil::create(
            $uuid,
            NombreCompleto::fromString('Test Usuario'),
            null,
            null,
            null,
            DireccionPostal::create(null, null, null),
        );
        $this->repo->savePerfil($perfil);

        $recuperado = $this->repo->findPerfilByUuid($uuid);

        $this->assertNotNull($recuperado);
        $this->assertSame('Test Usuario', $recuperado->nombre()->value());
    }

    public function test_exists_email(): void
    {
        $email = Email::fromString('test_' . uniqid() . '@finguer-test.com');
        $usuario = Usuario::create(
            UsuarioUuid::generate(),
            $email,
            Rol::Cliente,
            Locale::Es,
        );
        $this->repo->save($usuario);

        $this->assertTrue($this->repo->existsEmail($email));
        $this->assertFalse(
            $this->repo->existsEmail(
                Email::fromString('noexiste@finguer-test.com'),
            ),
        );
    }
}
