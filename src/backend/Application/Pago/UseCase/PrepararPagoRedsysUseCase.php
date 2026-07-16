<?php

declare(strict_types=1);

namespace App\Application\Pago\UseCase;

use App\Domain\Carrito\Repository\CarritoRepositoryInterface;
use App\Domain\Pago\Service\PasarelaPagoInterface;
use App\Domain\Pago\ValueObjects\PagoRedsysParams;
use App\Domain\Reserva\Service\LocalizadorGeneratorInterface;

final class PrepararPagoRedsysUseCase
{
    public function __construct(
        private readonly CarritoRepositoryInterface $carritoRepository,
        private readonly LocalizadorGeneratorInterface $localizadorGenerator,
        private readonly PasarelaPagoInterface $pasarelaPago,
    ) {}

    public function execute(string $session): PrepararPagoResult
    {
        if (trim($session) === '') {
            throw new \InvalidArgumentException('MISSING_SESSION');
        }

        $carrito = $this->carritoRepository->findBySession($session);
        if ($carrito === null) {
            throw new \InvalidArgumentException('CARRITO_NOT_FOUND');
        }

        if ($carrito->totalConIva() <= 0) {
            throw new \InvalidArgumentException('IMPORTE_INVALIDO');
        }

        $localizador = $this->localizadorGenerator->generar();

        $params = $this->pasarelaPago->prepararPago(
            $localizador,
            $carrito->totalConIva(),
        );

        return new PrepararPagoResult($params, $localizador);
    }
}
