<?php

function generarLocalizador(PDO $conn, ?DateTimeInterface $fechaReserva = null): string
{
    $tz = new DateTimeZone('Europe/Rome');
    $dt = $fechaReserva
        ? DateTimeImmutable::createFromInterface($fechaReserva)->setTimezone($tz)
        : new DateTimeImmutable('now', $tz);

    $pref = $dt->format('mdy'); // MMDDYY (ej: 121321)

    while (true) {
        $suf = str_pad((string) random_int(0, 9999), 4, '0', STR_PAD_LEFT); // 0000-9999
        $loc = $pref . $suf; // 10 dígitos

        // comprobación rápida (el UNIQUE es el definitivo)
        $st = $conn->prepare("SELECT 1 FROM parking_reservas WHERE localizador = ? LIMIT 1");
        $st->execute([$loc]);
        if (!$st->fetchColumn()) return $loc;
    }
}
