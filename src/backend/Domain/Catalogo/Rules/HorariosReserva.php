<?php

declare(strict_types=1);

namespace App\Domain\Catalogo\Rules;

use DateTimeInterface;

final class HorariosReserva
{
    public const TIPO_FINGUER_CLASS = 'RESERVA_FINGUER';
    public const TIPO_GOLD_CLASS = 'RESERVA_FINGUER_GOLD';

    /**
     * Horario disponible para Finguer Class (07:00 - 23:30, cada 30 min).
     */
    private const HORAS_FINGUER_CLASS = [
        '07:00',
        '07:30',
        '08:00',
        '08:30',
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
        '18:00',
        '18:30',
        '19:00',
        '19:30',
        '20:00',
        '20:30',
        '21:00',
        '21:30',
        '22:00',
        '22:30',
        '23:00',
        '23:30',
    ];

    /**
     * Horario disponible para Gold Class (08:00 - 21:00, cada 30 min).
     */
    private const HORAS_GOLD_CLASS = [
        '08:00',
        '08:30',
        '09:00',
        '09:30',
        '10:00',
        '10:30',
        '11:00',
        '11:30',
        '12:00',
        '12:30',
        '13:00',
        '13:30',
        '14:00',
        '14:30',
        '15:00',
        '15:30',
        '16:00',
        '16:30',
        '17:00',
        '17:30',
        '18:00',
        '18:30',
        '19:00',
        '19:30',
        '20:00',
        '20:30',
        '21:00',
    ];

    /**
     * Hora límite el 24 de diciembre (aplica todos los años): solo
     * se permiten horas <= a este valor.
     */
    private const HORA_LIMITE_24_DICIEMBRE = '18:00';

    /**
     * Devuelve las horas disponibles para un tipo de reserva en una fecha dada,
     * aplicando las restricciones de días especiales.
     *
     * @return string[]
     */
    public static function horasDisponibles(
        string $tipoReserva,
        DateTimeInterface $fecha,
    ): array {
        $horas = self::horasBase($tipoReserva);

        if (self::esVisperaNavidad($fecha)) {
            $horas = array_values(
                array_filter(
                    $horas,
                    static fn(string $hora): bool => $hora <=
                        self::HORA_LIMITE_24_DICIEMBRE,
                ),
            );
        }

        return $horas;
    }

    /**
     * Catálogo completo de horas para un tipo de reserva, sin aplicar
     * restricciones por fecha.
     *
     * @return string[]
     */
    public static function horasBase(string $tipoReserva): array
    {
        return $tipoReserva === self::TIPO_GOLD_CLASS
            ? self::HORAS_GOLD_CLASS
            : self::HORAS_FINGUER_CLASS;
    }

    /**
     * 24 de diciembre, cualquier año.
     */
    public static function esVisperaNavidad(DateTimeInterface $fecha): bool
    {
        return (int) $fecha->format('j') === 24 &&
            (int) $fecha->format('n') === 12;
    }

    /**
     * Comprueba si una hora concreta es válida para un tipo de reserva en una fecha dada.
     */
    public static function horaValida(
        string $tipoReserva,
        DateTimeInterface $fecha,
        string $hora,
    ): bool {
        return in_array(
            $hora,
            self::horasDisponibles($tipoReserva, $fecha),
            true,
        );
    }
}
