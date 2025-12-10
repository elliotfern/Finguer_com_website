<?php

/**
 * Devuelve el ID del usuario de backoffice a partir de la cookie `user_id`.
 * Devuelve null si no existe o no es numérico.
 */
function getUsuarioBackofficeIdFromCookie(): ?int
{
    if (!isset($_COOKIE['user_id'])) {
        return null;
    }

    $raw = $_COOKIE['user_id'];

    // seguridad básica: comprobar que sean solo dígitos
    if (!ctype_digit($raw)) {
        return null;
    }

    $id = (int)$raw;

    return $id > 0 ? $id : null;
}
