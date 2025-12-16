<?php

declare(strict_types=1);

final class ReservaNotFoundException extends RuntimeException {}

final class InvalidTransitionException extends RuntimeException
{
    public function __construct(
        public readonly int $id,
        public readonly string $from,
        public readonly string $to,
        public readonly array $allowedTo,
        string $message = 'INVALID_TRANSITION'
    ) {
        parent::__construct($message);
    }
}

final class EstadoConflictException extends RuntimeException
{
    public function __construct(
        public readonly int $id,
        public readonly string $expected,
        public readonly ?string $current,
        public readonly string $requested,
        string $message = 'CONFLICT'
    ) {
        parent::__construct($message);
    }
}
