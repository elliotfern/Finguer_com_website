<?php
declare(strict_types=1);

/**
 * UUID (con o sin guiones) -> BINARY(16)
 * Devuelve string binario de 16 bytes.
 */
function uuid_bin_from_string(string $uuid): string
{
    $hex = strtolower(trim($uuid));
    $hex = str_replace('-', '', $hex);

    if (!preg_match('/^[0-9a-f]{32}$/', $hex)) {
        throw new InvalidArgumentException("UUID inválido: " . $uuid);
    }

    $bin = hex2bin($hex);
    if ($bin === false || strlen($bin) !== 16) {
        throw new InvalidArgumentException("UUID inválido (no bin16): " . $uuid);
    }

    return $bin;
}

/**
 * BINARY(16) -> UUID string con guiones
 */
function uuid_string_from_bin(string $bin): string
{
    if (strlen($bin) !== 16) {
        throw new InvalidArgumentException("BINARY(16) inválido (len=" . strlen($bin) . ")");
    }

    $hex = bin2hex($bin);

    return sprintf(
        '%s-%s-%s-%s-%s',
        substr($hex, 0, 8),
        substr($hex, 8, 4),
        substr($hex, 12, 4),
        substr($hex, 16, 4),
        substr($hex, 20, 12)
    );
}

/**
 * Nullable helper:
 * - null/"" => null
 * - uuid => bin16
 */
function uuid_bin_from_nullable_string(?string $uuid): ?string
{
    if ($uuid === null) return null;
    $uuid = trim($uuid);
    if ($uuid === '') return null;
    return uuid_bin_from_string($uuid);
}
