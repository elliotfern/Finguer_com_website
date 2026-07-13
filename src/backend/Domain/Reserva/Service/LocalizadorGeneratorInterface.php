<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Service;

interface LocalizadorGeneratorInterface
{
    public function generar(?\DateTimeInterface $fechaReserva = null): string;
}
