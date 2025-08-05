import { PaymentData } from '../../types/interfaces';

export function imprimirDadesReserva(data: PaymentData): void {
  // Helper para actualizar un elemento por id
  const setText = (id: string, text: string) => {
    const el = document.getElementById(id);
    if (el) el.textContent = text;
  };

  // Tipo de reserva
  setText('tipoReserva', data.tipoReserva === 'finguer_class' ? 'Finguer Class' : data.tipoReserva === 'gold_finguer' ? 'Gold Finguer Class' : 'Reserva desconocida');

  // Fechas y horas
  setText('fechaEntrada', data.fechaEntrada);
  setText('horaEntrada', data.horaEntrada);
  setText('fechaSalida', data.fechaSalida);
  setText('horaSalida', data.horaSalida);

  // Días de reserva
  setText('diasReserva', String(data.diasReserva ?? ''));

  // Seguro de cancelación
  setText('seguroCancelacion', data.seguroCancelacion === '1' ? 'Contratado' : data.seguroCancelacion === '2' ? 'No contratado' : 'Desconocido');

  // Tipo limpieza
  const tipoLimpieza = data.limpieza === '0' ? 'No contratado' : data.limpieza === '15' ? 'Servicio de limpieza exterior' : data.limpieza === '35' ? 'Servicio de lavado exterior + aspirado tapicería interior' : data.limpieza === '95' ? 'Lavado PRO. Lo dejamos como nuevo' : 'Desconocido';
  setText('tipoLimpieza2', tipoLimpieza);

  // Precios
  setText('precioReserva', (Number(data.precioReserva) || 0).toFixed(2).replace('.', ','));
  setText('costeSeguro2', `${(Number(data.costeSeguro) || 0).toFixed(2).replace('.', ',')} € (sin IVA)`);
  setText('costeLimpieza', `${(Number(data.costoLimpiezaSinIva) || 0).toFixed(2).replace('.', ',')} € (sin IVA)`);
  setText('costeSubTotal', (Number(data.precioSubtotal) || 0).toFixed(2).replace('.', ','));
  setText('costeIva', (Number(data.costeIva) || 0).toFixed(2).replace('.', ','));

  const total = (Number(data.precioTotal) || 0).toFixed(2).replace('.', ',');
  setText('costeTotal', total);
  setText('costeTotal2', total);
  setText('costeTotal3', total);

  console.log('[imprimirDadesReserva] Datos pintados en el DOM');
}
