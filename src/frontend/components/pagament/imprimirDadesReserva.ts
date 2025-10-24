import { PaymentData } from '../../types/interfaces';

// --- Helpers Safari-safe ---
const setText = (id: string, value: unknown): void => {
  const el = document.getElementById(id);
  if (!el) return;
  const str = value === null || value === undefined ? '' : String(value);
  if (el instanceof HTMLInputElement || el instanceof HTMLTextAreaElement) {
    el.value = str;
  } else {
    el.textContent = str;
  }
};

// Acepta "12,34" o "12.34" y formatea "12,34"
const toMoney = (v: unknown): string => {
  const s = v === null || v === undefined ? '' : String(v).trim().replace(',', '.');
  const n = parseFloat(s);
  const num = isNaN(n) ? 0 : n;
  return num.toFixed(2).replace('.', ',');
};

export function imprimirDadesReserva(data: PaymentData): void {
  // Tipo de reserva
  let tipo = 'Reserva desconocida';
  if (data.tipoReserva === 'finguer_class') tipo = 'Finguer Class';
  else if (data.tipoReserva === 'gold_finguer') tipo = 'Gold Finguer Class';
  setText('tipoReserva', tipo);

  // Fechas y horas
  setText('fechaEntrada', data.fechaEntrada);
  setText('horaEntrada', data.horaEntrada);
  setText('fechaSalida', data.fechaSalida);
  setText('horaSalida', data.horaSalida);

  // Días de reserva (sin nullish coalescing)
  setText('diasReserva', data.diasReserva != null ? String(data.diasReserva) : '');

  // Seguro de cancelación
  let seguro = 'Desconocido';
  if (data.seguroCancelacion === '1') seguro = 'Contratado';
  else if (data.seguroCancelacion === '2') seguro = 'No contratado';
  setText('seguroCancelacion', seguro);

  // Tipo limpieza
  let tipoLimpieza = 'Desconocido';
  if (data.limpieza === '0') tipoLimpieza = 'No contratado';
  if (data.limpieza === '15') tipoLimpieza = 'Servicio de limpieza exterior';
  if (data.limpieza === '35') tipoLimpieza = 'Servicio de lavado exterior + aspirado tapicería interior';
  if (data.limpieza === '95') tipoLimpieza = 'Lavado PRO. Lo dejamos como nuevo';
  setText('tipoLimpieza2', tipoLimpieza);

  // Precios (parser tolerante y formateo consistente)
  setText('precioReserva', toMoney(data.precioReserva));
  setText('costeSeguro2', `${toMoney(data.costeSeguro)} € (sin IVA)`);
  setText('costeLimpieza', `${toMoney(data.costoLimpiezaSinIva)} € (sin IVA)`);
  setText('costeSubTotal', toMoney(data.precioSubtotal));
  setText('costeIva', toMoney(data.costeIva));

  const total = toMoney(data.precioTotal);
  setText('costeTotal', total);
  setText('costeTotal2', total);
  setText('costeTotal3', total);

  console.log('[imprimirDadesReserva] Datos pintados en el DOM');
}
