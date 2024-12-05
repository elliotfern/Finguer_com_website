import { parseDate } from './ValidarFechas';

export const calcularTotalDiasReserva = (fechaReserva: HTMLInputElement): number => {
  let diferenciaEnDias = 0;
  if (fechaReserva) {
    const fechas = fechaReserva.value.split(' - ');
    const fechaInicio = parseDate(fechas[0]); // Convierte la fecha de inicio
    const fechaFin = parseDate(fechas[1]); // Convierte la fecha de fin

    // Obtener la diferencia en milisegundos
    const diferenciaEnMilisegundos = fechaFin.getTime() - fechaInicio.getTime();

    // Convertir milisegundos a días
    diferenciaEnDias = Math.ceil(diferenciaEnMilisegundos / (1000 * 60 * 60 * 24));
  }

  // Sumar 1 para incluir el día de inicio
  return diferenciaEnDias + 1;
};
