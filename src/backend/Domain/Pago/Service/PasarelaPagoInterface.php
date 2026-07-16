<?php

declare(strict_types=1);

namespace App\Domain\Pago\Service;

use App\Domain\Pago\ValueObjects\PagoRedsysParams;

interface PasarelaPagoInterface
{
    /**
     * Prepara los parámetros firmados necesarios para redirigir al cliente
     * a la pasarela de pago, para un pedido identificado por $localizador.
     */
    public function prepararPago(
        string $localizador,
        float $importeConIva,
    ): PagoRedsysParams;
}
