import { calcularTotalReserva } from './CalcularTotalReserva';
import { PaymentData } from '../../../types/interfaces';

export const handleClickPagament = () => {
  // Llamar a calcularTotal y desestructurar el resultado
  const { precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, diasReserva } = calcularTotalReserva();

  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  let fechaEntrada = '';
  let fechaSalida = '';

  // Verificar si el input existe y tiene un valor
  if (fechaReservaElement && fechaReservaElement.value) {
    const fechas = fechaReservaElement.value.split(' to '); // Asegúrate de que el delimitador es 'to' si es lo que usas

    // Convertir las fechas en objetos Date
    const fechaInicio: Date = new Date(fechas[0]);
    const fechaFin: Date = new Date(fechas[1]);

    // Guardar las fechas de entrada y salida como cadenas legibles, si lo necesitas
    fechaEntrada = fechaInicio.toLocaleDateString(); // Convertir a string en formato de fecha
    fechaSalida = fechaFin.toLocaleDateString(); // Convertir a string en formato de fecha
  }

  const horaEntradaElement = (document.getElementById('horaEntrada') as HTMLInputElement | null)?.value || '';
  const horaSalidaElement = (document.getElementById('horaSalida') as HTMLInputElement | null)?.value || '';
  const limpiezaElement = (document.getElementById('limpieza') as HTMLInputElement | null)?.value || '';
  const tipoReservaElement = (document.getElementById('tipo_reserva') as HTMLInputElement | null)?.value || '';
  const seguroCancelacionElement = (document.querySelector('input[name="seguroCancelacion"]:checked') as HTMLInputElement | null)?.value || '';

  // Daades a guardar al LocalStorage
  const paymentData: PaymentData[] = [
    {
      precioTotal: parseFloat(precioTotal.toFixed(2)),
      costeSeguro: parseFloat(costeSeguro.toFixed(2)),
      precioReserva: parseFloat(precioReserva.toFixed(2)),
      costeIva: parseFloat(costeIva.toFixed(2)),
      precioSubtotal: parseFloat(precioSubtotal.toFixed(2)),
      costoLimpiezaSinIva: parseFloat(costoLimpiezaSinIva.toFixed(2)),
      fechaEntrada: fechaEntrada,
      fechaSalida: fechaSalida,
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
