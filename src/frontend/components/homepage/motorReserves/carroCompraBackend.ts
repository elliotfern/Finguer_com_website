// src/pages/home/motorReserves/carroCompraBackend.ts
import type { CotizarResponse } from './renderPrecio';

const API_URL = 'https://finguer.com/api/carro-compra/post';

function getOrCreateSessionKey(): string {
  const key = 'carro_session';
  let s = localStorage.getItem(key);
  if (s && s.length >= 6) return s;

  s = Math.random().toString(36).slice(2, 10);
  localStorage.setItem(key, s);
  return s;
}

function parseDateRangeValue(value: string): { start: string; end: string } | null {
  if (!value) return null;
  const parts = value.split(' to ');
  if (parts.length !== 2) return null;
  return { start: parts[0].trim(), end: parts[1].trim() };
}

function buildDateTime(dateYmd: string, hourHHmm: string): string {
  if (!dateYmd || !hourHHmm) return '';
  return `${dateYmd} ${hourHHmm}:00`;
}

function isCotizarResponse(x: unknown): x is CotizarResponse {
  if (!x || typeof x !== 'object') return false;
  const o = x as Record<string, unknown>;
  return typeof o.ok === 'boolean' && typeof o.diasReserva === 'number' && Array.isArray(o.lineas) && typeof o.subtotal === 'number' && typeof o.iva_total === 'number' && typeof o.total === 'number';
}

export async function cotizarCarroBackend(): Promise<CotizarResponse> {
  const tipoReservaEl = document.getElementById('tipo_reserva') as HTMLSelectElement | null;
  const limpiezaEl = document.getElementById('limpieza') as HTMLSelectElement | null;

  const fechaEl = document.getElementById('fecha_reserva') as HTMLInputElement | null;
  const horaEntradaEl = document.getElementById('horaEntrada') as HTMLSelectElement | null;
  const horaSalidaEl = document.getElementById('horaSalida') as HTMLSelectElement | null;

  const seguroSiEl = document.getElementById('seguroSi') as HTMLInputElement | null;
  const seguroNoEl = document.getElementById('seguroNo') as HTMLInputElement | null;

  if (!tipoReservaEl || !limpiezaEl || !fechaEl || !horaEntradaEl || !horaSalidaEl || !seguroSiEl || !seguroNoEl) {
    return { ok: false, diasReserva: 0, lineas: [], subtotal: 0, iva_total: 0, total: 0, error: 'Faltan elementos del formulario' };
  }

  const rango = parseDateRangeValue(fechaEl.value);
  if (!rango) {
    return { ok: false, diasReserva: 0, lineas: [], subtotal: 0, iva_total: 0, total: 0, error: 'Selecciona fechas' };
  }

  const horaEntrada = horaEntradaEl.value;
  const horaSalida = horaSalidaEl.value;
  if (!horaEntrada || !horaSalida) {
    return { ok: false, diasReserva: 0, lineas: [], subtotal: 0, iva_total: 0, total: 0, error: 'Selecciona horas' };
  }

  const tipoReserva = tipoReservaEl.value; // FINGUER_CLASS / RESERVA_FINGUER_GOLD
  const limpieza = limpiezaEl.value; // 0 o código
  const seguroCancelacion = seguroSiEl.checked ? 1 : 2;

  const session = getOrCreateSessionKey();

  const payload = {
    session,
    tipoReserva,
    limpieza,
    seguroCancelacion,
    fechaEntrada: buildDateTime(rango.start, horaEntrada),
    fechaSalida: buildDateTime(rango.end, horaSalida),
  };

  const resp = await fetch(API_URL, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(payload),
  });

  if (!resp.ok) {
    return {
      ok: false,
      diasReserva: 0,
      lineas: [],
      subtotal: 0,
      iva_total: 0,
      total: 0,
      error: `HTTP ${resp.status} al cotizar`,
    };
  }

  const json: unknown = await resp.json();

  if (!isCotizarResponse(json)) {
    return {
      ok: false,
      diasReserva: 0,
      lineas: [],
      subtotal: 0,
      iva_total: 0,
      total: 0,
      error: 'Respuesta inválida del servidor',
    };
  }

  return json;
}
