import { validarFechas } from "./ValidarFechas";

// Obtener los elementos del DOM
const horaEntradaSelect = document.getElementById('horaEntrada') as HTMLSelectElement;
const horaSalidaSelect = document.getElementById('horaSalida') as HTMLSelectElement;
const botonPagar = document.getElementById('pagar') as HTMLButtonElement;
const tipoReservaSelect = document.getElementById('tipo_reserva') as HTMLSelectElement; // Asumiendo que tienes un selector para el tipo de reserva

// Función para verificar si ambos campos de hora están seleccionados
const verificarSelecciones = (): boolean => {
  const horaEntradaValue = $('#horaEntrada').val() as string | null;
  const horaSalidaValue = $('#horaSalida').val() as string | null;
  return (horaEntradaValue !== null && horaEntradaValue !== '') &&
    (horaSalidaValue !== null && horaSalidaValue !== '');
};

// Función para validar las horas según el tipo de reserva
const validarHorasPorTipoReserva = (): boolean => {
  const horaEntradaValue = $('#horaEntrada').val() as string | null;
  const horaSalidaValue = $('#horaSalida').val() as string | null;
  const tipoReserva = tipoReservaSelect?.value;

  if (!horaEntradaValue || !horaSalidaValue) return false;

  if (tipoReserva === 'finguer_class') {
    // Validar horas para 'finguer_class' (entre 05:00 y 23:30)
    return esHoraValida(horaEntradaValue, '05:00', '23:30') && esHoraValida(horaSalidaValue, '05:00', '23:30');
  } else if (tipoReserva === 'gold_finguer') {
    // Validar horas para 'gold_finguer' (entre 07:00 y 21:30)
    return esHoraValida(horaEntradaValue, '08:00', '21:00') && esHoraValida(horaSalidaValue, '08:00', '21:00');
  }
  
  // Si el tipo de reserva no coincide con ninguno de los anteriores, retornar false
  return false;
};

// Función para validar si una hora está dentro de un rango
const esHoraValida = (hora: string, horaMinima: string, horaMaxima: string): boolean => {
  const [horaEntradaH, horaEntradaM] = hora.split(':').map(Number);
  const [horaMinimaH, horaMinimaM] = horaMinima.split(':').map(Number);
  const [horaMaximaH, horaMaximaM] = horaMaxima.split(':').map(Number);

  const minutosEntrada = horaEntradaH * 60 + horaEntradaM;
  const minutosMinima = horaMinimaH * 60 + horaMinimaM;
  const minutosMaxima = horaMaximaH * 60 + horaMaximaM;

  return minutosEntrada >= minutosMinima && minutosEntrada <= minutosMaxima;
};

// Función para actualizar la visibilidad del botón de pagar
export const actualizarBotonPagar = () => {
  // Verificar si las horas están seleccionadas, las fechas son válidas y las horas son válidas para el tipo de reserva
  if (verificarSelecciones() && validarFechas() && validarHorasPorTipoReserva()) {
    botonPagar.style.display = 'block'; // Mostrar el botón de pagar si las condiciones se cumplen
  } else {
    botonPagar.style.display = 'none'; // Ocultar el botón de pagar si no se cumplen las condiciones
  }
};

// Agregar event listeners a los selectores de hora y tipo de reserva
horaEntradaSelect.addEventListener('change', actualizarBotonPagar);
horaSalidaSelect.addEventListener('change', actualizarBotonPagar);
tipoReservaSelect?.addEventListener('change', actualizarBotonPagar); // Asegúrate de que este selector existe

// Inicialmente ocultar el botón de pagar
botonPagar.style.display = 'none';
