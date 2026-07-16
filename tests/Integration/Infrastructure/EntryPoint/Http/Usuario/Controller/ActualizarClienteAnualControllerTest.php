<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\EntryPoint\Http\Usuario\Controller;

use App\Domain\Shared\UsuarioUuid;
use PDO;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use Tests\Support\JwtTestTokenFactory;

#[Group('http')]
final class ActualizarClienteAnualControllerTest extends TestCase
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

    /**
     * Inserta usuario + perfil + abono directamente en BD,
     * simulando un cliente anual ya existente antes del test.
     */
    private function insertarClienteAnualDePrueba(
        string $email,
        string $matricula,
    ): string {
        $uuid = UsuarioUuid::generate();

        $stmt = $this->conn->prepare("
            INSERT INTO usuarios (uuid, email, estado, password, tipo_rol, locale)
            VALUES (:uuid, :email, 'activo', NULL, 'cliente_anual', 'es')
        ");
        $stmt->bindValue(':uuid', $uuid->toBytes(), PDO::PARAM_LOB);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $stmt = $this->conn->prepare("
            INSERT INTO usuarios_perfil (usuario_uuid, nombre)
            VALUES (:uuid, 'Nombre Original')
        ");
        $stmt->bindValue(':uuid', $uuid->toBytes(), PDO::PARAM_LOB);
        $stmt->execute();

        $abonoId = Uuid::uuid7()->getBytes();
        $stmt = $this->conn->prepare("
            INSERT INTO usuarios_abonos (id, usuario_uuid, estado, fecha_inicio, fecha_fin, limite_reservas, matricula)
            VALUES (:id, :uuid, 'activo', '2026-01-01', '2027-01-01', 10, :matricula)
        ");
        $stmt->bindValue(':id', $abonoId, PDO::PARAM_LOB);
        $stmt->bindValue(':uuid', $uuid->toBytes(), PDO::PARAM_LOB);
        $stmt->bindValue(':matricula', $matricula);
        $stmt->execute();

        return $uuid->toString();
    }

    private function apiBaseUrl(): string
    {
        return rtrim(
            $_ENV['API_BASE_TEST'] ?? 'http://127.0.0.1:8000/api',
            '/',
        );
    }

    private function put(string $url, array $payload): array
    {
        $token = JwtTestTokenFactory::generar(
            UsuarioUuid::generate()->toString(),
        );

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
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

    public function test_actualiza_cliente_anual_existente_correctamente(): void
    {
        $email = 'anual_upd_' . uniqid() . '@example.com';
        $matriculaOriginal = 'ORIG' . substr((string) time(), -4);
        $this->uuidCreado = $this->insertarClienteAnualDePrueba(
            $email,
            $matriculaOriginal,
        );

        $matriculaNueva = 'NEW' . substr((string) time(), -4);

        [$httpCode, $body] = $this->put(
            "{$this->apiBaseUrl()}/usuaris/put?type=clienteAnual-update",
            [
                'uuid' => $this->uuidCreado,
                'nombre' => 'Nombre Actualizado',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2027-02-01',
                'matricula' => $matriculaNueva,
            ],
        );

        $this->assertSame(200, $httpCode);
        $this->assertSame('success', $body['status'] ?? null);

        $uuidBin = UsuarioUuid::fromString($this->uuidCreado)->toBytes();

        $stmt = $this->conn->prepare(
            'SELECT nombre FROM usuarios_perfil WHERE usuario_uuid = :uuid',
        );
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();
        $this->assertSame('Nombre Actualizado', $stmt->fetchColumn());

        $stmt = $this->conn->prepare(
            'SELECT matricula FROM usuarios_abonos WHERE usuario_uuid = :uuid',
        );
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();
        $this->assertSame($matriculaNueva, $stmt->fetchColumn());

        // No debe haber creado un segundo abono (upsert, no insert)
        $stmt = $this->conn->prepare(
            'SELECT COUNT(*) FROM usuarios_abonos WHERE usuario_uuid = :uuid',
        );
        $stmt->bindValue(':uuid', $uuidBin, PDO::PARAM_LOB);
        $stmt->execute();
        $this->assertSame(1, (int) $stmt->fetchColumn());
    }

    public function test_devuelve_404_si_uuid_no_existe(): void
    {
        [$httpCode, $body] = $this->put(
            "{$this->apiBaseUrl()}/usuaris/put?type=clienteAnual-update",
            [
                'uuid' => UsuarioUuid::generate()->toString(),
                'nombre' => 'Nadie',
                'matricula' => 'XXXX999',
            ],
        );

        $this->assertSame(404, $httpCode);
        $this->assertSame('error', $body['status'] ?? null);
    }

    public function test_devuelve_error_si_falta_matricula(): void
    {
        $email = 'sin_matricula_upd_' . uniqid() . '@example.com';
        $this->uuidCreado = $this->insertarClienteAnualDePrueba(
            $email,
            'TEMP' . substr((string) time(), -4),
        );

        [$httpCode, $body] = $this->put(
            "{$this->apiBaseUrl()}/usuaris/put?type=clienteAnual-update",
            [
                'uuid' => $this->uuidCreado,
                'nombre' => 'Sin Matricula',
            ],
        );

        $this->assertContains($httpCode, [400, 422]);
        $this->assertSame('error', $body['status'] ?? null);
    }
}
