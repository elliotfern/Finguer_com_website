<?php

declare(strict_types=1);

namespace App\Application\Pago\UseCase;

use App\Domain\Pago\ValueObjects\PagoRedsysParams;

final class PrepararPagoResult
{
    public function __construct(
        public readonly PagoRedsysParams $params,
        public readonly string $localizador,
    ) {}
}
