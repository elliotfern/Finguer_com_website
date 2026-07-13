<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\MySql\Reserva;

use App\Domain\Reserva\Service\LocalizadorGeneratorInterface;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use PDO;

final class LocalizadorGenerator implements LocalizadorGeneratorInterface
{
    public function __construct(private readonly PDO $conn) {}

    public function generar(?DateTimeInterface $fechaReserva = null): string
    {
        $tz = new DateTimeZone('Europe/Rome');
        $dt = $fechaReserva
            ? DateTimeImmutable::createFromInterface(
                $fechaReserva,
            )->setTimezone($tz)
            : new DateTimeImmutable('now', $tz);

        $pref = $dt->format('mdy'); // MMDDYY (ej: 121321)

        while (true) {
            $suf = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT);
            $loc = $pref . $suf; // 10 dígitos

            $stmt = $this->conn->prepare(
                'SELECT 1 FROM parking_reservas WHERE localizador = ? LIMIT 1',
            );
            $stmt->execute([$loc]);

            if (!$stmt->fetchColumn()) {
                return $loc;
            }
        }
    }
}
