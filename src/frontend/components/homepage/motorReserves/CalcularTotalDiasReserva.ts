//import { parseDate } from './ValidarFechas';

export const calcularTotalDiasReserva = (fechaReserva: HTMLInputElement): number => {
  let diferenciaEnDias = 0;
  if (fechaReserva) {
    const fechas = fechaReserva.value.split(' to ');
    const fechaInicio = new Date(fechas[0]);
    const fechaFin = new Date(fechas[1]);

    // Obtener la diferencia en milisegundos
    const diferenciaEnMilisegundos = fechaFin.getTime() - fechaInicio.getTime();

    // Convertir milisegundos a días
    diferenciaEnDias = Math.ceil(diferenciaEnMilisegundos / (1000 * 60 * 60 * 24));
  }

  // Sumar 1 para incluir el día de inicio
  return diferenciaEnDias + 1;
};
