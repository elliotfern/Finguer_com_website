<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Domain\Shared\UsuarioUuid;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Tests\Support\JwtTestTokenFactory;

#[Group('http')]
final class CrearClienteAnualControllerTest extends TestCase
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
                'DELETE FROM usuarios_abonos WHERE usuario_uuid = :uuid',
            );
            $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
            $stmt->execute();

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

    public function test_crea_cliente_anual_correctamente(): void
    {
        $email = 'anual_' . uniqid() . '@example.com';

        [$httpCode, $body] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?type=clienteAnual-create",
            [
                'email' => $email,
                'nombre' => 'Cliente Anual Prueba',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2027-01-01',
                'matricula' => 'TEST' . substr((string) time(), -4),
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
        $this->assertSame('Cliente Anual Prueba', $stmt->fetchColumn());

        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM usuarios_abonos WHERE usuario_uuid = :uuid',
        );
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function test_devuelve_400_si_falta_matricula(): void
    {
        [$httpCode, $body] = $this->post(
            "{$this->apiBaseUrl()}/usuaris/post?clienteAnual-create",
            [
                'email' => 'sin_matricula_' . uniqid() . '@example.com',
                'nombre' => 'Sin Matricula',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2027-01-01',
            ],
        );

        $this->assertContains($httpCode, [400, 422]);
        $this->assertSame('error', $body['status'] ?? null);
    }
}
