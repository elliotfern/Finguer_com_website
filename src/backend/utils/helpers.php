<?php

function jsonResponse(array $payload, int $httpCode = 200): void
{
    http_response_code($httpCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
    exit();
}

function vp2_ok(string $message, array $data = [], array $extra = []): array
{
    return array_merge([
        'status'  => 'success',
        'message' => $message,
        'data'    => $data,
    ], $extra);
}

function vp2_err(string $message, string $code = '', array $extra = []): array
{
    return array_merge([
        'status'  => 'error',
        'code'    => $code,
        'message' => $message,
    ], $extra);
}
