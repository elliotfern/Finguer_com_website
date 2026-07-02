<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql;

use PDO;
use PDOException;

final class MysqlConnection
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $host = $_ENV['DB_HOST'] ?? '';
            $dbname = $_ENV['DB_DBNAME'] ?? '';
            $user = $_ENV['DB_USER'] ?? '';
            $pass = $_ENV['DB_PASS'] ?? '';

            if ($host === '' || $dbname === '' || $user === '') {
                throw new \RuntimeException(
                    'Faltan variables de entorno para la conexión a BD.',
                );
            }

            try {
                $pdo = new PDO(
                    "mysql:host={$host};dbname={$dbname};charset=utf8mb4",
                    $user,
                    $pass,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false,
                    ],
                );
                self::$instance = $pdo;
            } catch (PDOException $e) {
                throw new \RuntimeException(
                    'Error de conexión a BD: ' . $e->getMessage(),
                );
            }
        }

        return self::$instance;
    }

    public static function reset(): void
    {
        self::$instance = null;
    }
}
