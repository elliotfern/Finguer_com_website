import flatpickr from 'flatpickr';
import 'flatpickr/dist/flatpickr.css';
import { resetContadores } from './ResetContadores';
import { avisEspecialTancamentParking } from './avisEspecialTancamentParking';
import { scheduleCotizar } from './scheduleCotizar';

export const daterangepicker = () => {
  const startDate = new Date();
  startDate.setDate(startDate.getDate() + 2);

  const fechaReservaElement = document.querySelector('#fecha_reserva') as HTMLElement;

  if (!fechaReservaElement) return;

  flatpickr(fechaReservaElement, {
    mode: 'range',
    altInput: true,
    altFormat: 'd/m/Y',
    dateFormat: 'Y-m-d',
    minDate: startDate,
    maxDate: '2026-12-31',
    locale: {
      firstDayOfWeek: 1,
      weekdays: {
        shorthand: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
        longhand: ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'],
      },
      months: {
        shorthand: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        longhand: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
      },
    },
    onChange: (selectedDates: Date[], _dateStr, instance) => {
      const avisoDiv = document.getElementById('avis_especial') as HTMLElement | null;
      const detallesReserva = document.getElementById('importeReserva') as HTMLElement | null;

      if (selectedDates.length === 2) {
        const [start, end] = selectedDates;

        const fechasNoPermitidas = [
          { day: 25, month: 11 },
          { day: 26, month: 11 },
          { day: 31, month: 11 },
          { day: 1, month: 0 },
        ];

        const esFechaNoPermitida = (fecha: Date) => fechasNoPermitidas.some((f) => fecha.getDate() === f.day && fecha.getMonth() === f.month);

        if (esFechaNoPermitida(start) || esFechaNoPermitida(end)) {
          instance.clear();
          resetContadores();
          avisEspecialTancamentParking(true);
          if (detallesReserva) detallesReserva.style.display = 'none';
          return;
        }

        if (avisoDiv) avisoDiv.style.display = 'none';
        if (detallesReserva) detallesReserva.style.display = 'block';

        scheduleCotizar();
        return;
      }

      // selección incompleta / borrada
      if (avisoDiv) avisoDiv.style.display = 'none';
      if (detallesReserva) detallesReserva.style.display = 'block';

      // aquí puedes elegir:
      // - no cotizar hasta que haya 2 fechas (mi recomendación),
      // - o cotizar y que el backend devuelva error.
      // Yo recomiendo NO cotizar aún:
      resetContadores();
    },
  });
};
