// imprimirDadesReserva.ts
import { PaymentData } from '../../types/interfaces';

export const imprimirDadesReserva = (data: PaymentData[]): void => {
  // Verificar si hay datos
  if (data.length === 0) return;

  // Obtener el primer elemento del array (suponiendo que solo hay uno)
  const dades = data[0];

  // Asignar la información a los elementos de la página
  const tipoReservaElement = document.getElementById('tipoReserva');
  const fechaEntradaElement = document.getElementById('fechaEntrada');
  const horaEntradaElement = document.getElementById('horaEntrada');
  const fechaSalidaElement = document.getElementById('fechaSalida');
  const horaSalidaElement = document.getElementById('horaSalida');
  const diasReservaElement = document.getElementById('diasReserva');
  const precioReservaElement = document.getElementById('precioReserva');

  const precioReservaConvertit = dades.precioReserva.toFixed(2).replace('.', ',');

  const tipoReservaConvertido = dades.tipoReserva === 'finguer_class' ? 'Finguer Class' : dades.tipoReserva === 'gold_finguer' ? 'Gold Finguer Class' : 'Reserva desconocida';

  const seguroCancelacionElement = document.getElementById('seguroCancelacion');
  const seguroCancelacionConvertido = dades.seguroCancelacion === '1' ? 'Contratado' : dades.seguroCancelacion === '2' ? 'No contratado' : 'Desconocido';

  const costeSeguro2Element = document.getElementById('costeSeguro2');
  let costeSeguroConvertit = '';

  if (dades.costeSeguro === 0) {
    costeSeguroConvertit = '-';
  } else {
    costeSeguroConvertit = `${dades.costeSeguro.toFixed(2).replace('.', ',')}€ (sin IVA)`;
  }

  const tipoLimpiezaElement = document.getElementById('tipoLimpieza2');
  const tipoLimpiezaConvertido = dades.limpieza === '0' ? 'No contratado' : dades.limpieza === '15' ? 'Servicio de limpieza exterior' : dades.limpieza === '25' ? 'Servicio de lavado exterior + aspirado tapicería interior' : dades.limpieza === '55' ? 'Lavado PRO. Lo dejamos como nuevo' : 'Desconocido';

  const costeLimpiezaElement = document.getElementById('costeLimpieza');
  let costeLimpiezaConvertit = '';

  if (dades.costoLimpiezaSinIva === 0) {
    costeLimpiezaConvertit = '-';
  } else {
    costeLimpiezaConvertit = `${dades.costoLimpiezaSinIva.toFixed(2).replace('.', ',')}€ (sin IVA)`;
  }

  const costeSubTotalElement = document.getElementById('costeSubTotal');
  const costeSubTotalConvertit = dades.precioSubtotal.toFixed(2).replace('.', ',');

  const costeIvaElement = document.getElementById('costeIva');
  const costeIvaConvertit = dades.costeIva.toFixed(2).replace('.', ',');

  const costeTotalElement = document.getElementById('costeTotal');
  const costeTotalElement2 = document.getElementById('costeTotal2');
  const costeTotalElement3 = document.getElementById('costeTotal3');
  const costeTotalConvertit = dades.precioTotal.toFixed(2).replace('.', ',');

  if (tipoReservaElement) tipoReservaElement.textContent = tipoReservaConvertido;
  if (fechaEntradaElement) fechaEntradaElement.textContent = dades.fechaEntrada;
  if (horaEntradaElement) horaEntradaElement.textContent = dades.horaEntrada;
  if (fechaSalidaElement) fechaSalidaElement.textContent = dades.fechaSalida;
  if (horaSalidaElement) horaSalidaElement.textContent = dades.horaSalida;
  if (diasReservaElement) diasReservaElement.textContent = dades.diasReserva.toString();
  if (precioReservaElement) precioReservaElement.textContent = precioReservaConvertit;
  if (seguroCancelacionElement) seguroCancelacionElement.textContent = seguroCancelacionConvertido;
  if (costeSeguro2Element) costeSeguro2Element.textContent = `${costeSeguroConvertit}`;
  if (tipoLimpiezaElement) tipoLimpiezaElement.textContent = tipoLimpiezaConvertido;
  if (costeLimpiezaElement) costeLimpiezaElement.textContent = `${costeLimpiezaConvertit}`;
  if (costeSubTotalElement) costeSubTotalElement.textContent = costeSubTotalConvertit;
  if (costeIvaElement) costeIvaElement.textContent = costeIvaConvertit;
  if (costeTotalElement) costeTotalElement.textContent = costeTotalConvertit;
  if (costeTotalElement2) costeTotalElement2.textContent = costeTotalConvertit;
  if (costeTotalElement3) costeTotalElement3.textContent = costeTotalConvertit;
};
