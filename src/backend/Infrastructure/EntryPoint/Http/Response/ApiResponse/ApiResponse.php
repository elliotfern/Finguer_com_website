<?php

declare(strict_types=1);

namespace App\Infrastructure\EntryPoint\Http\Response\ApiResponse;

final class ApiResponse
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $extra
     */
    private function __construct(
        public readonly string $status,
        public readonly string $message,
        public readonly array $data,
        public readonly array $extra,
        public readonly int $httpCode,
    ) {}

    /**
     * @param array<string, mixed> $data
     * @param array<string, mixed> $extra
     */
    public static function ok(
        string $message,
        array $data = [],
        array $extra = [],
        int $httpCode = 200,
    ): self {
        return new self('success', $message, $data, $extra, $httpCode);
    }

    /**
     * @param array<string, mixed> $extra
     */
    public static function error(
        string $message,
        string $code = '',
        array $extra = [],
        int $httpCode = 400,
    ): self {
        return new self(
            'error',
            $message,
            [],
            array_merge(['code' => $code], $extra),
            $httpCode,
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(
            [
                'status' => $this->status,
                'message' => $this->message,
                'data' => $this->data,
            ],
            $this->extra,
        );
    }
}
