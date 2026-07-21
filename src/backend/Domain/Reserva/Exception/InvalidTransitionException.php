<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Exception;

final class InvalidTransitionException extends \RuntimeException
{
    /**
     * @param string[] $allowedTo
     */
    public function __construct(
        public readonly int $id,
        public readonly string $from,
        public readonly string $to,
        public readonly array $allowedTo,
    ) {
        parent::__construct(
            "Transición no permitida para la reserva {$id}: de '{$from}' a '{$to}'.",
        );
    }
}
