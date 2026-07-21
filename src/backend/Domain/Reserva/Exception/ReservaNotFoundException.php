<?php

declare(strict_types=1);

namespace App\Domain\Reserva\Exception;

final class ReservaNotFoundException extends \RuntimeException
{
    private function __construct(
        public readonly ?int $id,
        public readonly ?string $localizador,
        string $message,
    ) {
        parent::__construct($message);
    }

    public static function porId(int $id): self
    {
        return new self($id, null, "Reserva no encontrada: {$id}");
    }

    public static function porLocalizador(string $localizador): self
    {
        return new self(
            null,
            $localizador,
            "Reserva no encontrada: {$localizador}",
        );
    }
}
