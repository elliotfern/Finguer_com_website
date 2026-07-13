<?php

declare(strict_types=1);

use App\Infrastructure\Persistence\MySql\Reserva\LocalizadorGenerator;
use PHPUnit\Framework\TestCase;

class LocalizadorGeneratorTest extends TestCase
{
    private PDO $conn;
    private LocalizadorGenerator $generator;

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
        $this->generator = new LocalizadorGenerator($this->conn);
    }

    public function test_genera_localizador_con_formato_correcto(): void
    {
        $fecha = new \DateTimeImmutable('2026-12-13');
        $localizador = $this->generator->generar($fecha);

        $this->assertMatchesRegularExpression('/^\d{10}$/', $localizador);
        $this->assertStringStartsWith('121326', $localizador);
    }

    public function test_genera_localizador_unico_si_ya_existe_uno(): void
    {
        $fecha = new \DateTimeImmutable('2026-12-13');

        // Forzamos una colisión: insertamos un localizador ya generado
        $primero = $this->generator->generar($fecha);

        $stmt = $this->conn->prepare(
            "INSERT INTO parking_reservas
                (usuario_uuid, localizador, estado, estado_vehiculo, fecha_reserva, entrada_prevista, salida_prevista)
             VALUES (UNHEX(REPLACE(UUID(), '-', '')), :localizador, 'pendiente', 'pendiente_entrada', NOW(), NOW(), NOW())",
        );
        $stmt->execute([':localizador' => $primero]);

        $segundo = $this->generator->generar($fecha);

        $this->assertNotSame($primero, $segundo);
        $this->assertStringStartsWith('121326', $segundo);

        // Limpieza
        $this->conn->exec(
            "DELETE FROM parking_reservas WHERE localizador IN ('{$primero}', '{$segundo}')",
        );
    }
}
