<?php

declare(strict_types=1);

namespace App\Domain\Pago\ValueObjects;

final class PagoRedsysParams
{
    public function __construct(
        public readonly string $params,
        public readonly string $signature,
    ) {}
}
