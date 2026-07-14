<?php

declare(strict_types=1);

namespace App\Application\Carrito\Exception;

final class ReglaNegocioException extends \RuntimeException
{
    public function __construct(
        public readonly string $codigoRegla,
        string $mensaje,
    ) {
        parent::__construct($mensaje);
    }
}
