<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Exception;

final class EstadoConflictException extends \RuntimeException
{
    public function __construct(
        public readonly int $id,
        public readonly string $expected,
        public readonly ?string $current,
        public readonly string $requested,
    ) {
        parent::__construct(
            "Conflicto de estado en la reserva {$id}: se esperaba '{$expected}' pero es '{$current}'.",
        );
    }
}
