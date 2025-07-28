import { avisEspecialTancamentParking } from './avisEspecialTancamentParking';

export const seleccionaHoraTipoReserva = () => {
  const tipoReservaSelect = document.getElementById('tipo_reserva') as HTMLSelectElement;
  const horaEntradaSelect = document.getElementById('horaEntrada') as HTMLSelectElement;
  const horaSalidaSelect = document.getElementById('horaSalida') as HTMLSelectElement;
  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  let fechaInicio: Date | undefined;
  let fechaFin: Date | undefined;

  if (fechaReservaElement && fechaReservaElement.value) {
    const fechas = fechaReservaElement.value.split(' to ');
    fechaInicio = new Date(fechas[0]);
    fechaFin = new Date(fechas[1]);
  }

  // Función para filtrar las horas según las reglas de fechas específicas
  const filtrarHorasPorFecha = (fecha: Date, horas: string[]): string[] => {
    const dia = fecha.getDate();
    const mes = fecha.getMonth() + 1;
    const año = fecha.getFullYear();

    let showAviso = false;

    // Definir las condiciones específicas para cada fecha y hora
    if (año === 2024 || año === 2026) {
      if (mes === 12 && dia === 24) {
        // Si es 24 de diciembre, mostrar aviso solo si la hora seleccionada es <= 18:00
        showAviso = horas.some((hora) => hora <= '18:00');
      } else if (mes === 12 && dia === 26) {
        // Si es 26 de diciembre, mostrar aviso solo si la hora seleccionada es >= 12:00
        showAviso = horas.some((hora) => hora >= '12:00');
      } else if (mes === 12 && dia === 31) {
        // Si es 31 de diciembre, mostrar aviso solo si la hora seleccionada es <= 18:00
        showAviso = horas.some((hora) => hora <= '18:00');
      } else if (mes === 1 && dia === 1 && año === 2026) {
        // Si es 1 de enero de 2025, mostrar aviso solo si la hora seleccionada es >= 12:00
        showAviso = horas.some((hora) => hora >= '12:00');
      }
    }

    // Llamar a la función avisEspecialTancamentParking con el valor adecuado
    avisEspecialTancamentParking(showAviso);

    // Filtrar las horas según las condiciones
    if (showAviso) {
      if (mes === 12 && dia === 24) {
        return horas.filter((hora) => hora <= '18:00');
      } else if (mes === 12 && dia === 26) {
        return horas.filter((hora) => hora >= '12:00');
      } else if (mes === 12 && dia === 31) {
        return horas.filter((hora) => hora <= '18:00');
      } else if (mes === 1 && dia === 1 && año === 2025) {
        return horas.filter((hora) => hora >= '12:00');
      }
    }

    // Si no se aplica ningún filtro especial, devolver todas las horas
    return horas;
  };

  // Opciones de horarios por tipo de reserva
  const horasFinguerClass: string[] = ['05:00', '05:30', '06:00', '06:30', '07:00', '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00', '21:30', '22:00', '22:30', '23:00', '23:30'];

  const horasGoldFinguer: string[] = ['08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30', '21:00'];

  // Función para llenar las opciones de hora en un select específico
  const llenarHoras = (tipo: string, select: HTMLSelectElement, fecha: Date) => {
    let horas = tipo === 'gold_finguer' ? horasGoldFinguer : horasFinguerClass;
    horas = filtrarHorasPorFecha(fecha, horas);

    select.innerHTML = '<option selected value="">Selecciona una hora:</option>';
    horas.forEach((hora) => {
      const option = document.createElement('option');
      option.value = hora;
      option.textContent = hora;
      select.appendChild(option);
    });
  };

  // Inicializar las opciones
  if (fechaInicio) {
    llenarHoras(tipoReservaSelect.value, horaEntradaSelect, fechaInicio);
  }
  if (fechaFin) {
    llenarHoras(tipoReservaSelect.value, horaSalidaSelect, fechaFin);
  }

  // Cambiar las horas según el tipo de reserva
  tipoReservaSelect.addEventListener('change', () => {
    if (fechaInicio) {
      llenarHoras(tipoReservaSelect.value, horaEntradaSelect, fechaInicio);
    }
    if (fechaFin) {
      llenarHoras(tipoReservaSelect.value, horaSalidaSelect, fechaFin);
    }
  });

  // Cambiar las horas según las fechas seleccionadas
  if (fechaReservaElement) {
    fechaReservaElement.addEventListener('change', () => {
      if (fechaReservaElement.value) {
        const fechas = fechaReservaElement.value.split(' to ');

        // Determinar si se modificó la fecha de inicio o fin
        const nuevaFechaInicio = new Date(fechas[0]);
        const nuevaFechaFin = new Date(fechas[1]);

        if (nuevaFechaInicio.getTime() !== fechaInicio?.getTime()) {
          fechaInicio = nuevaFechaInicio;
          llenarHoras(tipoReservaSelect.value, horaEntradaSelect, fechaInicio);
        }

        if (nuevaFechaFin.getTime() !== fechaFin?.getTime()) {
          fechaFin = nuevaFechaFin;
          llenarHoras(tipoReservaSelect.value, horaSalidaSelect, fechaFin);
        }
      }
    });
  }
};
