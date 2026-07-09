<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\EntryPoint\Http\Usuario;

use App\Domain\Shared\UsuarioUuid;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\JwtTestTokenFactory;

#[Group('http')]
final class CrearUsuarioControllerTest extends TestCase
{
    private PDO $conn;
    private ?string $uuidCreado = null;

    protected function setUp(): void
    {
        parent::setUp();

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
    }

    protected function tearDown(): void
    {
        if ($this->uuidCreado !== null) {
            $uuidBin = UsuarioUuid::fromString($this->uuidCreado)->toBytes();

            $stmt = $this->conn->prepare(
                'DELETE FROM usuarios_perfil WHERE usuario_uuid = :uuid',
            );
            $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
            $stmt->execute();

            $stmt = $this->conn->prepare(
                'DELETE FROM usuarios WHERE uuid = :uuid',
            );
            $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
            $stmt->execute();
        }
        parent::tearDown();
    }

    private function buscarUuidPorEmail(string $email): ?string
    {
        $stmt = $this->conn->prepare(
            'SELECT uuid FROM usuarios WHERE email = :email',
        );
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $bin = $stmt->fetchColumn();

        return $bin ? UsuarioUuid::fromBytes($bin)->toString() : null;
    }

    private function apiBaseUrl(): string
    {
        return rtrim(
            $_ENV['API_BASE_TEST'] ?? 'http://127.0.0.1:8000/api',
            '/',
        );
    }

    private function post(string $url, array $payload): array
    {
        $token = JwtTestTokenFactory::generar(
            UsuarioUuid::generate()->toString(),
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
        ]);
        curl_setopt($ch, CURLOPT_COOKIE, "token={$token}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $body = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        return [$httpCode, json_decode((string) $body, true)];
    }

    public function test_crea_usuario_nuevo_correctamente(): void
    {
        $email = 'nuevo_' . uniqid() . '@example.com';

        [$httpCode, $body] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?type=usuarios-create",
            [
                'email' => $email,
                'nombre' => 'Usuario De Prueba',
                'tipo_rol' => 'cliente',
                'locale' => 'es',
            ],
        );

        $this->assertSame(200, $httpCode);
        $this->assertSame('success', $body['status'] ?? null);

        $this->uuidCreado = $this->buscarUuidPorEmail($email);
        $this->assertNotNull(
            $this->uuidCreado,
            'El usuario no se guardó en BD',
        );

        $uuidBin = UsuarioUuid::fromString($this->uuidCreado)->toBytes();

        $stmt = $this->conn->prepare(
            'SELECT nombre FROM usuarios_perfil WHERE usuario_uuid = :uuid',
        );
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();
        $this->assertSame('Usuario De Prueba', $stmt->fetchColumn());
    }

    public function test_devuelve_usuario_existente_si_email_ya_existe(): void
    {
        $email = 'existente_' . uniqid() . '@example.com';

        // Primera llamada: crea el usuario
        [$httpCode1, $body1] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?type=usuarios-create",
            ['email' => $email, 'nombre' => 'Primero'],
        );
        $this->assertSame(200, $httpCode1);
        $this->uuidCreado = $this->buscarUuidPorEmail($email);

        // Segunda llamada: mismo email, no debería crear un duplicado
        [$httpCode2, $body2] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?type=usuarios-create",
            ['email' => $email, 'nombre' => 'Segundo'],
        );

        $this->assertSame(
            $body1['usuario_uuid_hex'],
            $body2['usuario_uuid_hex'],
            'No debería crear un segundo usuario con el mismo email',
        );

        // Solo debe existir UN registro con este email
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM usuarios WHERE email = :email',
        );
        $stmt->bindValue(':email', $email);
        $stmt->execute();
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function test_devuelve_400_si_email_invalido(): void
    {
        [$httpCode, $body] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?type=usuarios-create",
            ['email' => 'esto-no-es-un-email', 'nombre' => 'Test'],
        );

        $this->assertSame(400, $httpCode);
        $this->assertSame('error', $body['status'] ?? null);
    }
}
