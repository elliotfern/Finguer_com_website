import { parseData } from '../../../utils/parseData';

export const calcularTotalDiasReserva = (fechaReserva: HTMLInputElement): number => {
  if (!fechaReserva || !fechaReserva.value) return 0;

  const fechas = fechaReserva.value.split(' to ');
  const fechaInicio = parseData(fechas[0]);
  const fechaFin = parseData(fechas[1]);

  if (isNaN(fechaInicio.getTime()) || isNaN(fechaFin.getTime())) {
    console.error('Fecha inválida:', fechas);
    return 0;
  }

  // Diferencia en milisegundos
  const diferenciaEnMilisegundos = fechaFin.getTime() - fechaInicio.getTime();

  // Convertir milisegundos a días
  const diferenciaEnDias = Math.ceil(diferenciaEnMilisegundos / (1000 * 60 * 60 * 24));

  // Sumar 1 día de inicio
  return diferenciaEnDias + 1;
};
