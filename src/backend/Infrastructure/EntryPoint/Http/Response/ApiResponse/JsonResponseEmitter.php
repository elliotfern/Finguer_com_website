<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Response\ApiResponse;

final class JsonResponseEmitter
{
    public function emit(ApiResponse $response): void
    {
        http_response_code($response->httpCode);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($response->toArray(), JSON_UNESCAPED_UNICODE);
    }
}
