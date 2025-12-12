import { resetContadores } from './ResetContadores';

function parseDateRange(value: string): { start: string; end: string } | null {
  const parts = value.split(' to ');
  if (parts.length !== 2) return null;
  return { start: parts[0].trim(), end: parts[1].trim() };
}

function buildDateTime(dateYmd: string, hourHHmm: string): Date | null {
  if (!dateYmd || !hourHHmm) return null;
  // dateYmd viene como "YYYY-MM-DD" (flatpickr dateFormat)
  // hourHHmm viene como "HH:MM"
  const iso = `${dateYmd}T${hourHHmm}:00`;
  const d = new Date(iso);
  return Number.isNaN(d.getTime()) ? null : d;
}

export const validarFechas = (): boolean => {
  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;
  const horaEntradaEl = document.getElementById('horaEntrada') as HTMLSelectElement | null;

  const mensajeErrorElement = document.getElementById('mensaje_error');

  if (!fechaReservaElement || !fechaReservaElement.value) {
    // No hay fechas aún: no permitir pagar
    return false;
  }

  const rango = parseDateRange(fechaReservaElement.value);
  if (!rango) {
    if (mensajeErrorElement) mensajeErrorElement.textContent = 'Selecciona un rango de fechas válido.';
    resetContadores();
    return false;
  }

  const horaEntrada = horaEntradaEl?.value ?? '';
  // Si aún no hay hora, no podemos validar las 12h correctamente => bloquea pagar, sin error agresivo
  if (!horaEntrada) return false;

  const fechaInicio = buildDateTime(rango.start, horaEntrada);
  const fechaFin = buildDateTime(rango.end, horaEntrada); // aquí solo nos importa que sea posterior; la hora real de salida se valida aparte

  if (!fechaInicio || !fechaFin) {
    if (mensajeErrorElement) mensajeErrorElement.textContent = 'Fechas u horas no válidas.';
    resetContadores();
    return false;
  }

  const ahora = new Date();
  const horasDiferencia = (fechaInicio.getTime() - ahora.getTime()) / (1000 * 60 * 60);

  if (fechaInicio.getTime() <= ahora.getTime() || horasDiferencia < 12) {
    if (mensajeErrorElement) {
      mensajeErrorElement.textContent = 'Tu fecha de llegada al parking debe ser al menos 12 horas después de la hora actual.';
    }
    resetContadores();
    return false;
  }

  // fin debe ser posterior a inicio (en fecha; tu lógica antigua era <=)
  if (fechaFin.getTime() <= fechaInicio.getTime()) {
    if (mensajeErrorElement) mensajeErrorElement.textContent = 'La fecha de fin debe ser posterior a la fecha de inicio.';
    resetContadores();
    return false;
  }

  if (mensajeErrorElement) mensajeErrorElement.textContent = '';
  return true;
};
