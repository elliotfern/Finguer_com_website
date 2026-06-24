<?php

function clearAuthCookies(): void
{
    // Cookie compartida entre subdominios
    setcookie('token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'domain'   => '.finguer.com',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    // Posible cookie antigua host-only
    setcookie('token', '', [
        'expires'  => time() - 3600,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    unset($_COOKIE['token']);
}