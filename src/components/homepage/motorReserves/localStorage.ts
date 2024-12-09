import { calcularTotalReserva } from './CalcularTotalReserva';
import { PaymentData } from '../../../types/interfaces';

export const handleClickPagament = () => {
  // Llamar a calcularTotal y desestructurar el resultado
  const { precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, diasReserva } = calcularTotalReserva();

  // Extreure les dades de les dates entrada i sortida
  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  let fechaEntradaElement = '';
  let fechaSalidaElement = '';

  if (fechaReservaElement) {
    const fechas = fechaReservaElement.value.split(' - ');
    fechaEntradaElement = fechas[0] || '';
    fechaSalidaElement = fechas[1] || '';
  }

  const horaEntradaElement = (document.getElementById('horaEntrada') as HTMLInputElement | null)?.value || '';
  const horaSalidaElement = (document.getElementById('horaSalida') as HTMLInputElement | null)?.value || '';
  const limpiezaElement = (document.getElementById('limpieza') as HTMLInputElement | null)?.value || '';
  const tipoReservaElement = (document.getElementById('tipo_reserva') as HTMLInputElement | null)?.value || '';
  const seguroCancelacionElement = (document.querySelector('input[name="seguroCancelacion"]:checked') as HTMLInputElement | null)?.value || '';

  // Daades a guardar al LocalStorage
  const paymentData: PaymentData[] = [
    {
      precioTotal: precioTotal,
      costeSeguro: costeSeguro,
      precioReserva: precioReserva,
      costeIva: costeIva,
      precioSubtotal: precioSubtotal,
      costoLimpiezaSinIva: costoLimpiezaSinIva,
      fechaEntrada: fechaEntradaElement,
      fechaSalida: fechaSalidaElement,
      horaEntrada: horaEntradaElement,
      horaSalida: horaSalidaElement,
      limpieza: limpiezaElement,
      tipoReserva: tipoReservaElement,
      diasReserva: diasReserva,
      seguroCancelacion: seguroCancelacionElement,
    },
  ];

  try {
    // Guardar los datos en localStorage
    localStorage.setItem('paymentData', JSON.stringify(paymentData));

    // Redirigir a la página de pago
    window.location.href = '/pago';
  } catch (error) {
    console.error('Error al guardar datos en localStorage:', error);
  }
};
