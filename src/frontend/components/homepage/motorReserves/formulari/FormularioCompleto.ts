import { resetContadores } from './ResetContadores';

function parseDateRange(value: string): { start: string; end: string } | null {
    const parts = value.split(' to ');
    if (parts.length !== 2) return null;
    return { start: parts[0].trim(), end: parts[1].trim() };
}

function buildDateTime(dateYmd: string, hourHHmm: string): Date | null {
    if (!dateYmd || !hourHHmm) return null;
    const iso = `${dateYmd}T${hourHHmm}:00`;
    const d = new Date(iso);
    return Number.isNaN(d.getTime()) ? null : d;
}

/**
 * Comprueba que el formulario está completo y bien formado:
 * fechas seleccionadas, hora de entrada presente, y fechas parseables.
 * Las reglas de negocio (antelación mínima, fechas no disponibles, etc.)
 * las valida el backend en /cotizar.
 */
export const FormularioCompleto = (): boolean => {
    const fechaReservaElement = document.getElementById(
        'fecha_reserva'
    ) as HTMLInputElement | null;
    const horaEntradaEl = document.getElementById(
        'horaEntrada'
    ) as HTMLSelectElement | null;

    const mensajeErrorElement = document.getElementById('mensaje_error');

    if (!fechaReservaElement || !fechaReservaElement.value) {
        return false;
    }

    const rango = parseDateRange(fechaReservaElement.value);
    if (!rango) {
        if (mensajeErrorElement)
            mensajeErrorElement.textContent =
                'Selecciona un rango de fechas válido.';
        resetContadores();
        return false;
    }

    const horaEntrada = horaEntradaEl?.value ?? '';
    if (!horaEntrada) return false;

    const fechaInicio = buildDateTime(rango.start, horaEntrada);
    const fechaFin = buildDateTime(rango.end, horaEntrada);

    if (!fechaInicio || !fechaFin) {
        if (mensajeErrorElement)
            mensajeErrorElement.textContent = 'Fechas u horas no válidas.';
        resetContadores();
        return false;
    }

    if (mensajeErrorElement) mensajeErrorElement.textContent = '';
    return true;
};
