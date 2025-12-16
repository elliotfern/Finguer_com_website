import { ApiOk } from '../../../types/api';
import { obrirFinestra, tancarFinestra } from './finestraEmergent/finestraEmergent';
import { carregarDadesTaulaReserves } from './taulaReserves/taulaReserves';

// TIPADO (mismo shape que usa tu popup)
type DeviceInfo = {
  dispositiu?: string;
  navegador?: string;
  sistema_operatiu?: string;
  ip?: string;
};

type DeviceInfoInput = DeviceInfo[] | DeviceInfo | null;

function isRecord(v: unknown): v is Record<string, unknown> {
  return typeof v === 'object' && v !== null;
}

function isApiOk<T>(v: unknown): v is ApiOk<T> {
  if (!isRecord(v)) return false;
  return v.status === 'success' && 'data' in v;
}

function isDeviceInfo(v: unknown): v is DeviceInfo {
  if (!isRecord(v)) return false;
  const keys: (keyof DeviceInfo)[] = ['dispositiu', 'navegador', 'sistema_operatiu', 'ip'];
  return keys.every((k) => !(k in v) || typeof v[k] === 'string');
}

function isDeviceInfoArray(v: unknown): v is DeviceInfo[] {
  return Array.isArray(v) && v.every(isDeviceInfo);
}

type ReservaIdData = { rows?: unknown };

function extractRowsFromVp2(json: unknown): DeviceInfo[] | null {
  if (!isApiOk<ReservaIdData>(json)) return null;
  const data = json.data;
  if (!isRecord(data)) return null;

  const rows = data.rows;
  if (isDeviceInfoArray(rows)) return rows;
  return null;
}

const DEVICE_INFO_ENDPOINT = (id: string) => `${window.location.origin}/api/intranet/reserves/get/?type=reservaId&id=${encodeURIComponent(id)}`;

async function obtenirDeviceInfo(id: string): Promise<DeviceInfoInput> {
  try {
    const url = DEVICE_INFO_ENDPOINT(id);
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json: unknown = await res.json();

    // Solo backend nuevo: { status: 'success', data: { rows: [...] } }
    const rows = extractRowsFromVp2(json);
    if (rows) return rows;

    return null;
  } catch (e) {
    console.warn('No se pudo obtener DeviceInfo desde la API:', e);
    return null;
  }
}

export const reserves = (estatParking: string) => {
  carregarDadesTaulaReserves(estatParking);

  // 👇 HAZ EL HANDLER ASYNC
  document.addEventListener('click', async (event: MouseEvent) => {
    const target = event.target as HTMLElement;

    // Verificar si el elemento clickeado tiene la clase 'obrir-finestra-btn'
    if (target.classList.contains('obrir-finestra-btn')) {
      const id = target.getAttribute('data-id');
      if (id) {
        // 👉 Trae la info de la API y pásala al popup
        const deviceInfo = await obtenirDeviceInfo(id);
        obrirFinestra(event as MouseEvent, id, deviceInfo);
      }
    }
    // Verificar si el elemento clickeado tiene la clase 'tancar-finestra-btn'
    else if (target.classList.contains('tancar-finestra-btn')) {
      tancarFinestra();
    }
  });
};
