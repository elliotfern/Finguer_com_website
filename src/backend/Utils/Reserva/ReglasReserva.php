<?php

declare(strict_types=1);

namespace App\Utils\Reserva;

use DateTime;
use DateTimeInterface;
use DateTimeZone;

final class ReglasReserva
{
    public const TIMEZONE = 'Europe/Madrid';
    public const DIAS_ANTELACION_MINIMA = 2;
    public const FECHA_MAXIMA = '2027-12-31';

    /**
     * Fechas en las que el parking permanece cerrado (Navidad / Año Nuevo).
     * Formato: día (1-31), mes (1-12). Se aplica todos los años.
     */
    private const FECHAS_NO_DISPONIBLES = [
        ['dia' => 25, 'mes' => 12], // Navidad
        ['dia' => 26, 'mes' => 12],
        ['dia' => 31, 'mes' => 12], // Nochevieja
        ['dia' => 1, 'mes' => 1], // Año nuevo
    ];

    /**
     * Fecha mínima seleccionable (hoy + antelación mínima), formato Y-m-d.
     */
    public static function fechaMinima(): string
    {
        return new DateTime(
            '+' . self::DIAS_ANTELACION_MINIMA . ' days',
            new DateTimeZone(self::TIMEZONE),
        )->format('Y-m-d');
    }

    /**
     * Lista de fechas no disponibles, pensada para que el frontend
     * pueda deshabilitarlas en el calendario.
     */
    public static function fechasNoDisponibles(): array
    {
        return self::FECHAS_NO_DISPONIBLES;
    }

    public static function esFechaNoDisponible(DateTimeInterface $fecha): bool
    {
        foreach (self::FECHAS_NO_DISPONIBLES as $f) {
            if (
                (int) $fecha->format('j') === $f['dia'] &&
                (int) $fecha->format('n') === $f['mes']
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * Valida un rango entrada/salida según las reglas de negocio.
     *
     * @return array{valido: bool, codigo?: string, mensaje?: string}
     */
    public static function validarRango(
        DateTimeInterface $entrada,
        DateTimeInterface $salida,
    ): array {
        if (
            self::esFechaNoDisponible($entrada) ||
            self::esFechaNoDisponible($salida)
        ) {
            return [
                'valido' => false,
                'codigo' => 'fechas_no_disponibles',
                'mensaje' =>
                    'El parking permanece cerrado los días 25, 26 y 31 de diciembre y 1 de enero.',
            ];
        }

        if ($salida <= $entrada) {
            return [
                'valido' => false,
                'codigo' => 'rango_invalido',
                'mensaje' =>
                    'La fecha de salida debe ser posterior a la de entrada.',
            ];
        }

        $minima = new DateTime(
            self::fechaMinima(),
            new DateTimeZone(self::TIMEZONE),
        );

        if ($entrada < $minima) {
            return [
                'valido' => false,
                'codigo' => 'antelacion_minima',
                'mensaje' =>
                    'La reserva debe hacerse con al menos ' .
                    self::DIAS_ANTELACION_MINIMA .
                    ' días de antelación.',
            ];
        }

        return ['valido' => true];
    }
}
