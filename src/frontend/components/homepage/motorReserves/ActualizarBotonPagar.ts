import { validarFechas } from './ValidarFechas';

const horaEntradaSelect = document.getElementById('horaEntrada') as HTMLSelectElement | null;
const horaSalidaSelect = document.getElementById('horaSalida') as HTMLSelectElement | null;
const botonPagar = document.getElementById('pagar') as HTMLButtonElement | null;
const tipoReservaSelect = document.getElementById('tipo_reserva') as HTMLSelectElement | null;

let backendOk = false; // 👈 se activa cuando /cotizar responde ok

const verificarSelecciones = (): boolean => {
  const horaEntradaValue = horaEntradaSelect?.value ?? '';
  const horaSalidaValue = horaSalidaSelect?.value ?? '';
  return horaEntradaValue !== '' && horaSalidaValue !== '';
};

const esHoraValida = (hora: string, horaMinima: string, horaMaxima: string): boolean => {
  const [h, m] = hora.split(':').map(Number);
  const [minH, minM] = horaMinima.split(':').map(Number);
  const [maxH, maxM] = horaMaxima.split(':').map(Number);

  const minutos = h * 60 + m;
  const minutosMin = minH * 60 + minM;
  const minutosMax = maxH * 60 + maxM;

  return minutos >= minutosMin && minutos <= minutosMax;
};

const validarHorasPorTipoReserva = (): boolean => {
  const horaEntradaValue = horaEntradaSelect?.value ?? '';
  const horaSalidaValue = horaSalidaSelect?.value ?? '';
  const tipoReserva = tipoReservaSelect?.value ?? '';

  if (!horaEntradaValue || !horaSalidaValue) return false;

  if (tipoReserva === 'RESERVA_FINGUER') {
    return esHoraValida(horaEntradaValue, '05:00', '23:30') && esHoraValida(horaSalidaValue, '05:00', '23:30');
  }

  if (tipoReserva === 'RESERVA_FINGUER_GOLD') {
    return esHoraValida(horaEntradaValue, '08:00', '21:00') && esHoraValida(horaSalidaValue, '08:00', '21:00');
  }

  return false;
};

/**
 * Llama a esto desde scheduleCotizar:
 * - setBackendOk(true) cuando el backend responde ok
 * - setBackendOk(false) si error/invalid
 */
export const setBackendOk = (ok: boolean): void => {
  backendOk = ok;
  actualizarBotonPagar();
};

// ahora no recibe params: usa backendOk interno
export const actualizarBotonPagar = (): void => {
  if (!botonPagar) return;

  const puedePagar = backendOk && verificarSelecciones() && validarFechas() && validarHorasPorTipoReserva();

  botonPagar.style.display = puedePagar ? 'block' : 'none';
};

// listeners (solo si existen)
horaEntradaSelect?.addEventListener('change', () => {
  backendOk = false; // 👈 cambia algo, obligamos a re-cotizar antes de pagar
  actualizarBotonPagar();
});
horaSalidaSelect?.addEventListener('change', () => {
  backendOk = false;
  actualizarBotonPagar();
});
tipoReservaSelect?.addEventListener('change', () => {
  backendOk = false;
  actualizarBotonPagar();
});

// Inicialmente oculto
if (botonPagar) {
  botonPagar.style.display = 'none';
}
