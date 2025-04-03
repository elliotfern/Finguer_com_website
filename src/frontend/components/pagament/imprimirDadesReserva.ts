import { PaymentData } from '../../types/interfaces';

export const imprimirDadesReserva = (data: PaymentData): void => {
  // Primer declarem les variables on imprimir els valors:
  const tipoReservaElement = document.getElementById('tipoReserva');
  const fechaEntradaElement = document.getElementById('fechaEntrada');
  const horaEntradaElement = document.getElementById('horaEntrada');
  const fechaSalidaElement = document.getElementById('fechaSalida');
  const horaSalidaElement = document.getElementById('horaSalida');
  const diasReservaElement = document.getElementById('diasReserva');
  const precioReservaElement = document.getElementById('precioReserva');
  const seguroCancelacionElement = document.getElementById('seguroCancelacion');
  const costeSeguro2Element = document.getElementById('costeSeguro2');
  const costeLimpiezaElement = document.getElementById('costeLimpieza');
  const tipoLimpiezaElement = document.getElementById('tipoLimpieza2');
  const costeSubTotalElement = document.getElementById('costeSubTotal');
  const costeIvaElement = document.getElementById('costeIva');
  const costeTotalElement = document.getElementById('costeTotal');
  const costeTotalElement2 = document.getElementById('costeTotal2');
  const costeTotalElement3 = document.getElementById('costeTotal3');

  // ara imprimim els valors:
  const dades = data;

  // 1. Tipus de reserva:
  if (tipoReservaElement) {
    const tipoReservaConvertido = dades.tipoReserva === 'finguer_class' ? 'Finguer Class' : dades.tipoReserva === 'gold_finguer' ? 'Gold Finguer Class' : 'Reserva desconocida';
    tipoReservaElement.textContent = tipoReservaConvertido;
  }

  // 2. Data i hora entrada // data i hora de sortida:
  if (fechaEntradaElement && horaEntradaElement && fechaSalidaElement && horaSalidaElement) {
    fechaEntradaElement.textContent = dades.fechaEntrada;
    horaEntradaElement.textContent = dades.horaEntrada;
    fechaSalidaElement.textContent = dades.fechaSalida;
    horaSalidaElement.textContent = dades.horaSalida;
  }

  // 3. Dies reserva
  if (diasReservaElement) {
    diasReservaElement.textContent = dades.diasReserva.toString();
  }

  // 4. Assegurança cancelacio
  if (seguroCancelacionElement) {
    const seguroCancelacionConvertido = dades.seguroCancelacion === '1' ? 'Contratado' : dades.seguroCancelacion === '2' ? 'No contratado' : 'Desconocido';
    seguroCancelacionElement.textContent = seguroCancelacionConvertido;
  }

  // 5. Tipus de neteja
  if (tipoLimpiezaElement) {
    const tipoLimpiezaConvertido = dades.limpieza === '0' ? 'No contratado' : dades.limpieza === '15' ? 'Servicio de limpieza exterior' : dades.limpieza === '35' ? 'Servicio de lavado exterior + aspirado tapicería interior' : dades.limpieza === '95' ? 'Lavado PRO. Lo dejamos como nuevo' : 'Desconocido';
    tipoLimpiezaElement.textContent = tipoLimpiezaConvertido;
  }

  // Ara els valors numerics
  // 6. Cost assegurança
  if (costeSeguro2Element) {
    const costeSeguro = dades.costeSeguro != null ? Number(dades.costeSeguro) : 0;
    costeSeguro2Element.textContent = `${costeSeguro.toFixed(2).replace('.', ',')}€ (sin IVA)`;
  }

  // 7. Preu reserva
  if (precioReservaElement) {
    const preuReserva = dades.precioReserva != null ? Number(dades.precioReserva) : 0;
    precioReservaElement.textContent = preuReserva.toFixed(2).replace('.', ',');
  }

  // 8. Cost neteja
  if (costeLimpiezaElement) {
    const costeLimpiezaConvertit = dades.costoLimpiezaSinIva != null ? Number(dades.costoLimpiezaSinIva) : 0;
    costeLimpiezaElement.textContent = `${costeLimpiezaConvertit.toFixed(2).replace('.', ',')}€ (sin IVA)`;
  }

  // 9. Preu subTotal
  if (costeSubTotalElement) {
    const costeSubTotalConvertit = dades.precioSubtotal != null ? Number(dades.precioSubtotal) : 0;
    costeSubTotalElement.textContent = costeSubTotalConvertit.toFixed(2).replace('.', ',');
  }

  // 10. Preu IVA
  if (costeIvaElement) {
    const costeIvaConvertit = dades.costeIva != null ? Number(dades.costeIva) : 0;
    costeIvaElement.textContent = costeIvaConvertit.toFixed(2).replace('.', ',');
  }
  // 12.Preu cost total - 1
  if (costeTotalElement) {
    const costeTotalConvertit = dades.precioTotal != null ? Number(dades.precioTotal) : 0;
    costeTotalElement.textContent = costeTotalConvertit.toFixed(2).replace('.', ',');
  }
  // 12.Preu cost total - 2
  if (costeTotalElement2) {
    const costeTotalConvertit = dades.precioTotal != null ? Number(dades.precioTotal) : 0;
    costeTotalElement2.textContent = costeTotalConvertit.toFixed(2).replace('.', ',');
  }
  // 12.Preu cost total - 3
  if (costeTotalElement3) {
    const costeTotalConvertit = dades.precioTotal != null ? Number(dades.precioTotal) : 0;
    costeTotalElement3.textContent = costeTotalConvertit.toFixed(2).replace('.', ',');
  }
};
