import { validarFechas } from './ValidarFechas';
import { calcularTotalDiasReserva } from './CalcularTotalDiasReserva';
import { calcularPrecioSinIva } from './CalcularPrecioSinIva';
import { calcularPrecioConIva } from './CalcularPrecioConIva';

export const calcularTotalReserva = (): {
  precioTotal: number;
  costeSeguro: number;
  precioReserva: number;
  costeIva: number;
  precioSubtotal: number;
  costoLimpiezaSinIva: number;
  diasReserva: number;
} => {
  let precioTotal = 0;
  let precioSubtotal = 0;
  let costeIva = 0;
  let costeSeguro = 0;
  let diasReserva = 0;
  let precioReserva = 0;
  let costoLimpiezaSinIva = 0;

  const costoDia = 5;
  const porcentajeIva = 0.21;
  const costeReservaFinguerClass = 30;
  const costeReservaGoldClass = 50;

  // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
  if (!validarFechas()) {
    return { precioTotal: 0, costeSeguro: 0, precioReserva: 0, costeIva: 0, precioSubtotal: 0, costoLimpiezaSinIva: 0, diasReserva: 0 };
  }

  // Cálculo de días
  const fechaReserva = document.getElementById('fecha_reserva') as HTMLInputElement | null;
  if (fechaReserva) {
    diasReserva = calcularTotalDiasReserva(fechaReserva);
  }

  // ----- COSTE BASE SIN IVA (sin redondear todavía) -----
  const tipoReservaElement = document.getElementById('tipo_reserva') as HTMLSelectElement | null;
  let costeBaseSinIva = 0;

  if (tipoReservaElement) {
    const tipoReserva = tipoReservaElement.value;
    if (tipoReserva === 'finguer_class') {
      costeBaseSinIva = costeReservaFinguerClass / (1 + porcentajeIva);
    } else if (tipoReserva === 'gold_finguer') {
      costeBaseSinIva = costeReservaGoldClass / (1 + porcentajeIva);
    }
  }

  // ----- RECARGO POR DÍAS EXTRA (a partir del 4º día) -----
  const recargoDiasSinIva = diasReserva > 3 ? (diasReserva - 3) * (costoDia / (1 + porcentajeIva)) : 0;

  precioReserva = costeBaseSinIva + recargoDiasSinIva;

  // ----- LIMPIEZA -----
  const limpiezaElement = document.getElementById('limpieza') as HTMLInputElement | null;
  if (limpiezaElement) {
    const costoLimpieza = parseInt(limpiezaElement.value, 10) || 0;
    if ([15, 35, 95].includes(costoLimpieza)) {
      costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
    }
  }

  precioSubtotal = precioReserva + costoLimpiezaSinIva;

  // ----- SEGURO -----
  const seguroCancelacionElement = document.querySelector('input[name="seguroCancelacion"]:checked') as HTMLInputElement | null;
  const seguroCancelacion = seguroCancelacionElement ? seguroCancelacionElement.value : null;
  if (seguroCancelacion === '1') {
    if (precioSubtotal <= 50) {
      costeSeguro = 15;
    } else {
      costeSeguro = precioSubtotal * 0.1;
    }
    precioSubtotal += costeSeguro;
  }

  // ---- IVA Y TOTAL ----
  const resultadoConIva = calcularPrecioConIva(precioSubtotal, porcentajeIva);
  costeIva = resultadoConIva.iva;
  precioTotal = +resultadoConIva.precioConIva.toFixed(2); // redondeo final exacto

  // ---- DOM OUTPUT (igual que antes) ----
  const costeReservaElement = document.getElementById('costeReserva');
  const costeSeguroElement = document.getElementById('costeSeguro');
  const costeLimpiezaElement = document.getElementById('costeLimpieza');
  const totalElement = document.getElementById('total');
  const precioSubTotalElement = document.getElementById('subTotal');
  const ivaElement = document.getElementById('precio_iva');
  const reservaElement = document.getElementById('resumenReserva');
  const horaEntradaElement = document.getElementById('horaEntrada') as HTMLInputElement | null;
  const horaSalidaElement = document.getElementById('horaSalida') as HTMLInputElement | null;

  const horaEntradaValue = horaEntradaElement ? horaEntradaElement.value : null;
  const horaSalidaValue = horaSalidaElement ? horaSalidaElement.value : null;

  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;
  let fechaEntrada = '';
  let fechaSalida = '';

  if (fechaReservaElement && fechaReservaElement.value) {
    const fechas = fechaReservaElement.value.split(' to ');
    const fechaInicio: Date = new Date(fechas[0]);
    const fechaFin: Date = new Date(fechas[1]);
    fechaEntrada = fechaInicio.toLocaleDateString();
    fechaSalida = fechaFin.toLocaleDateString();
  }

  const diaEntradaElement = document.getElementById('diaEntrada');
  const diaSalidaElement = document.getElementById('diaSalida');

  if (reservaElement && diaEntradaElement && diaSalidaElement && totalElement && precioSubTotalElement && ivaElement && costeReservaElement && costeSeguroElement && costeLimpiezaElement && fechaEntrada && fechaSalida) {
    reservaElement.innerHTML = `Detalles de la Reserva:`;
    diaEntradaElement.innerHTML = `<strong>Día de entrada:</strong> ${fechaEntrada} // ${horaEntradaValue}h.`;
    diaSalidaElement.innerHTML = `<strong>Día de salida:</strong> ${fechaSalida} // ${horaSalidaValue}h.`;
    costeReservaElement.innerHTML = `<strong>Coste Reserva (${diasReserva} ${diasReserva > 1 ? 'días' : 'día'}):</strong> ${precioReserva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
    costeSeguroElement.innerHTML = costeSeguro !== 0 ? `Coste Seguro: ${costeSeguro.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)` : '';
    costeLimpiezaElement.innerHTML = costoLimpiezaSinIva !== 0 ? `Coste Limpieza: ${costoLimpiezaSinIva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)` : ``;
    precioSubTotalElement.innerHTML = `Subtotal: ${precioSubtotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
    ivaElement.innerHTML = `IVA (21%): ${costeIva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
    totalElement.innerHTML = `Precio Total: ${precioTotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € IVA incluido`;

    precioSubTotalElement.style.display = 'block';
    totalElement.style.display = 'block';
    ivaElement.style.display = 'block';
    costeReservaElement.style.display = 'block';
    costeSeguroElement.style.display = 'block';
    costeLimpiezaElement.style.display = 'block';
    reservaElement.style.display = 'block';
    diaEntradaElement.style.display = 'block';
    diaSalidaElement.style.display = 'block';
  }

  return { precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, diasReserva };
};
