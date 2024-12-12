import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { calcularTotalReserva } from './CalcularTotalReserva';
import { actualizarBotonPagar } from './ActualizarBotonPagar';
import { showPrice } from './ShowPrice';

export const daterangepicker = () => {
  const startDate = new Date();
  startDate.setDate(startDate.getDate() + 2); // Fecha de inicio + 2 días
  //const endDate = new Date(startDate); // Fecha de fin igual a la de inicio

  // Seleccionamos el input de fecha y aplicamos flatpickr
  const fechaReservaElement = document.querySelector('#fecha_reserva') as HTMLElement; // Obtén el elemento como HTMLElement

  if (fechaReservaElement) {
    flatpickr(fechaReservaElement, {
      mode: 'range',
      dateFormat: 'Y-m-d',
      minDate: startDate,
      locale: {
        firstDayOfWeek: 1, // Primer día de la semana: lunes
        weekdays: {
          shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
          longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
        },
        months: {
          shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
          longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
        },
      },
      onChange: (selectedDates: Date[]) => {
        // Verificar que se han seleccionado al menos dos fechas
        if (selectedDates.length === 2) {
          // Calcular el costo total cuando se selecciona un rango de fechas
          calcularTotalReserva();
          actualizarBotonPagar();
          showPrice();
        }
      },
    });
  }
};
