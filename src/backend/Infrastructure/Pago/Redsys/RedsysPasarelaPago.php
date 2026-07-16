<?php

declare(strict_types=1);

namespace App\Infrastructure\Pago\Redsys;

use App\Domain\Pago\Service\PasarelaPagoInterface;
use App\Domain\Pago\ValueObjects\PagoRedsysParams;
use App\Infrastructure\Pago\Vendor\RedsysAPI;

final class RedsysPasarelaPago implements PasarelaPagoInterface
{
    public function __construct(
        private readonly string $merchantCode,
        private readonly string $terminal,
        private readonly string $secretKey,
        private readonly string $merchantUrl,
        private readonly string $urlOk,
        private readonly string $urlKo,
    ) {}

    public static function fromEnv(): self
    {
        return new self(
            merchantCode: $_ENV['MERCHANTCODE'],
            terminal: $_ENV['TERMINAL'],
            secretKey: $_ENV['KEY'],
            merchantUrl: $_ENV['REDSYS_NOTIFICACIO'],
            urlOk: $_ENV['URLOK'],
            urlKo: $_ENV['URLKO'],
        );
    }

    public function prepararPago(
        string $localizador,
        float $importeConIva,
    ): PagoRedsysParams {
        $amount = (int) round($importeConIva * 100);

        $api = new RedsysAPI();
        $api->setParameter('DS_MERCHANT_AMOUNT', (string) $amount);
        $api->setParameter('DS_MERCHANT_ORDER', $localizador);
        $api->setParameter('DS_MERCHANT_MERCHANTCODE', $this->merchantCode);
        $api->setParameter('DS_MERCHANT_CURRENCY', '978');
        $api->setParameter('DS_MERCHANT_TRANSACTIONTYPE', '0');
        $api->setParameter('DS_MERCHANT_TERMINAL', $this->terminal);
        $api->setParameter('DS_MERCHANT_MERCHANTURL', $this->merchantUrl);
        $api->setParameter('DS_MERCHANT_URLOK', $this->urlOk);
        $api->setParameter('DS_MERCHANT_URLKO', $this->urlKo);

        $params = $api->createMerchantParameters();
        $signature = $api->createMerchantSignature($this->secretKey);

        return new PagoRedsysParams($params, $signature);
    }
}
