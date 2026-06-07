<?php

namespace App\Payments;

class RedsysSignatureVerifier
{
    private string $secretKey;

    public function __construct(string $secretKey)
    {
        $this->secretKey = $secretKey;
    }

    public function check(string $merchantParams, string $signature, string $order): bool
    {
        $decoded = json_decode(base64_decode($merchantParams), true);

        if (!$decoded) {
            return false;
        }

        // 1. crear clave derivada por order
        $key = base64_decode($this->secretKey);

        $iv = str_repeat("\0", 8);

        $cipher = openssl_encrypt(
            $order,
            'DES-EDE3-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        $expected = base64_encode(hash_hmac('sha256', $merchantParams, $cipher, true));

        // 2. Redsys usa variante URL-safe
        $expected = strtr($expected, '+/', '-_');

        return hash_equals($expected, $signature);
    }
}