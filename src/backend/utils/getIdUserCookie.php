<?php

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Convierte UUID canónico (xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx) a BINARY(16).
 */
function uuidStrToBin(string $uuid): string
{
    $hex = str_replace('-', '', strtolower(trim($uuid)));

    // Validación rápida: 32 hex chars
    if (!preg_match('/^[0-9a-f]{32}$/', $hex)) {
        throw new InvalidArgumentException('UUID inválido: ' . $uuid);
    }

    $bin = hex2bin($hex);
    if ($bin === false || strlen($bin) !== 16) {
        throw new InvalidArgumentException('No se pudo convertir UUID a binario');
    }

    return $bin;
}

/**
 * Devuelve el usuario UUID en formato BINARY(16) a partir del JWT guardado en la cookie "token".
 * Retorna null si no hay cookie, el token es inválido/expirado, o no cumple issuer.
 */
function getUsuarioBackofficeIdFromCookie(): ?string
{
    if (empty($_COOKIE['token'])) {
        return null;
    }

    $token = (string) $_COOKIE['token'];

    try {
        $decoded = JWT::decode(
            $token,
            new Key($_ENV['JWT_SECRET'], 'HS256')
        );

        // issuer esperado
        if (!isset($decoded->iss) || $decoded->iss !== 'finguer-intranet') {
            return null;
        }

        // sub = UUID string
        if (!isset($decoded->sub) || !is_string($decoded->sub) || $decoded->sub === '') {
            return null;
        }

        return uuidStrToBin($decoded->sub);

    } catch (\Throwable $e) {
        // token inválido / expirado / firma incorrecta / uuid inválido / etc.
        return null;
    }
}
