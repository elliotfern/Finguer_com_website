<?php

declare(strict_types=1);

namespace App\Application\Carrito\DTO;

final class GuardarCarritoDTO
{
    public function __construct(
        public readonly string $session,
        public readonly string $tipoReserva,
        public readonly string $limpiezaCodigo,
        public readonly bool $seguroCancelacion,
        public readonly string $fechaEntrada,
        public readonly string $fechaSalida,
    ) {}

    public static function fromArray(array $input): self
    {
        $limpiezaCodigo = trim((string) ($input['limpieza'] ?? '0'));
        if ($limpiezaCodigo === '') {
            $limpiezaCodigo = '0';
        }

        return new self(
            session: trim((string) ($input['session'] ?? '')),
            tipoReserva: strtoupper(
                trim((string) ($input['tipoReserva'] ?? '')),
            ),
            limpiezaCodigo: $limpiezaCodigo,
            seguroCancelacion: (int) ($input['seguroCancelacion'] ?? 0) === 1,
            fechaEntrada: trim((string) ($input['fechaEntrada'] ?? '')),
            fechaSalida: trim((string) ($input['fechaSalida'] ?? '')),
        );
    }
}
