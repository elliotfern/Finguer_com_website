import { resetContadores } from './ResetContadores';

export const validarFechas = () => {
  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  if (fechaReservaElement && fechaReservaElement.value) {
    // Obtener las fechas desde el input como string
    const fechas = fechaReservaElement.value.split(' to '); // Cambié el separador a ' to '
    console.log(fechas);

    // Obtener las fechas de inicio y fin
    const fechaInicio = new Date(fechas[0]);
    const fechaFin = new Date(fechas[1]);

    // Obtener la fecha actual
    const fechaActual = new Date();

    // Calcular la diferencia en horas considerando tanto la fecha como la hora actual
    const horasDiferencia = (fechaInicio.getTime() - fechaActual.getTime()) / (1000 * 60 * 60);

    // Si la fecha de inicio es anterior a la fecha actual o dentro de las 12 horas
    if (fechaInicio < fechaActual || horasDiferencia < 12) {
      const mensajeErrorElement = document.getElementById('mensaje_error');
      if (mensajeErrorElement) {
        mensajeErrorElement.textContent = 'Tu fecha de llegada al parking debe ser al menos 12 horas después de la hora actual.';
      }
      resetContadores(); // Llamar a la función para restablecer contadores
      return false;
    }

    // Comprobar si fechaFin es el mismo día o anterior a fechaInicio
    const isSameOrBefore = (date1: Date, date2: Date): boolean => {
      // Comparar solo la fecha (sin horas)
      return date1.getTime() <= date2.getTime();
    };

    // Usar la función para comprobar
    if (isSameOrBefore(fechaFin, fechaInicio)) {
      const mensajeErrorElement = document.getElementById('mensaje_error');
      if (mensajeErrorElement) {
        mensajeErrorElement.textContent = 'La fecha de fin debe ser posterior a la fecha de inicio.';
      }

      resetContadores(); // Llamar a la función para restablecer contadores
      return false;
    }

    const mensajeErrorElement = document.getElementById('mensaje_error');
    if (mensajeErrorElement) {
      mensajeErrorElement.textContent = '';
    }

    return true;
  }
};
