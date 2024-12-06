import $ from 'jquery';
import 'daterangepicker/daterangepicker.css';
import 'daterangepicker';

export const daterangepicker = () => {
  const startDate = new Date();
  startDate.setDate(startDate.getDate() + 2); // Fecha de inicio + 2 días
  const endDate = new Date(startDate); // Fecha de fin igual a la de inicio

  $('#fecha_reserva').daterangepicker({
    autoApply: true,
    endDate: formatDate(endDate),
    minDate: formatDate(new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate())), // Fecha mínima// No permitir seleccionar fechas anteriores a la fecha actual
    locale: {
      format: 'DD-MM-YYYY',
      firstDay: 1, // Configura el primer día de la semana como lunes (0 para domingo, 1 para lunes, 2 para martes, etc.)
      cancelLabel: 'Cancelar',
      applyLabel: 'Aplicar', // Etiqueta del botón Aplicar
      daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'], // Nombres de los días de la semana
      monthNames: [
        // Nombres de los meses
        'Enero',
        'Febrero',
        'Marzo',
        'Abril',
        'Mayo',
        'Junio',
        'Julio',
        'Agosto',
        'Septiembre',
        'Octubre',
        'Noviembre',
        'Diciembre',
      ],
    },
  });
};

// Función para formatear una fecha como DD-MM-YYYY
const formatDate = (date: Date): string => {
  const day = String(date.getDate()).padStart(2, '0');
  const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses comienzan desde 0
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
};
