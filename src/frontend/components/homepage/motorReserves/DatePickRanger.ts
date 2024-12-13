import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { calcularTotalReserva } from './CalcularTotalReserva';
import { actualizarBotonPagar } from './ActualizarBotonPagar';
import { showPrice } from './ShowPrice';
import { resetContadores } from './ResetContadores';
import { avisEspecialTancamentParking } from './avisEspecialTancamentParking';

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
      onChange: function (selectedDates: Date[], dateStr, instance) {
        // Si el usuario selecciona 25 de diciembre como inicio o fin, desactívalo
        // Verificar si se seleccionaron dos fechas
        const avisoDiv = document.getElementById('avis_especial') as HTMLElement;
        const detallesReserva = document.getElementById('importeReserva') as HTMLElement;

        if (selectedDates.length === 2) {
          const [startDate, endDate] = selectedDates;

          // Fechas no permitidas
          const fechasNoPermitidas = [
            { day: 25, month: 11 }, // 25 de diciembre
          ];

          // Función para verificar si una fecha es no permitida
          const esFechaNoPermitida = (fecha: Date) => fechasNoPermitidas.some((f) => fecha.getDate() === f.day && fecha.getMonth() === f.month);

          if (esFechaNoPermitida(startDate) || esFechaNoPermitida(endDate)) {
            instance.clear(); // Limpiar selección si la fecha es no permitida
            resetContadores();
            avisEspecialTancamentParking(true);
            if (detallesReserva) {
              detallesReserva.style.display = 'none';
            }
          } else if (avisoDiv) {
            // Ocultar el aviso si las fechas seleccionadas son válidas
            avisoDiv.style.display = 'none';
            if (detallesReserva) {
              detallesReserva.style.display = 'block';
            }
            // Llama a las funciones auxiliares
            calcularTotalReserva();
            actualizarBotonPagar();
            showPrice();
          }
        } else if (avisoDiv) {
          // Ocultar el aviso si la selección se borra o es incompleta
          avisoDiv.style.display = 'none';
          if (detallesReserva) {
            detallesReserva.style.display = 'block';
          }
          calcularTotalReserva();
          actualizarBotonPagar();
          showPrice();
        }
      },
    });
  }
};
