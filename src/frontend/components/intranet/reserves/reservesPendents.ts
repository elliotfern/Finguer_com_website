import { apiUrl } from '../../../config/globals';
import { ApiOk } from '../../../types/api';
import { obrirFinestra, tancarFinestra } from './finestraEmergent/popUpReserva';
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

const DEVICE_INFO_ENDPOINT = (id: string) => `${apiUrl}/intranet/reserves/get/?type=reservaId&id=${encodeURIComponent(id)}`;

async function obtenirDeviceInfo(id: string): Promise<DeviceInfoInput> {
  try {
    const url = DEVICE_INFO_ENDPOINT(id);
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const json: unknown = await res.json();

    const rows = extractRowsFromVp2(json);
    if (rows) return rows;

    return null;
  } catch (e) {
    console.warn('No se pudo obtener DeviceInfo desde la API:', e);
    return null;
  }
}

// ✅ IMPORTANTE: listener global solo 1 vez
let reservesClickBound = false;

export const reserves = (estatParking: string, tipo?: string) => {
  carregarDadesTaulaReserves(estatParking, tipo);

  if (reservesClickBound) return;
  reservesClickBound = true;

  document.addEventListener('click', async (event: MouseEvent) => {
    const target = event.target as HTMLElement | null;
    if (!target) return;

    // ✅ Mejor con closest: funciona si clicas en un icono dentro del botón, etc.
    const openBtn = target.closest('.obrir-finestra-btn') as HTMLElement | null;
    if (openBtn) {
      const id = openBtn.getAttribute('data-id');
      if (!id) return;

      const estado = openBtn.getAttribute('data-estado'); // 👈 nuevo
      const deviceInfo = await obtenirDeviceInfo(id);

      obrirFinestra(event, id, deviceInfo, estado); // 👈 nuevo
      return;
    }

    // cerrar
    if (target.closest('.tancar-finestra-btn')) {
      tancarFinestra();
    }
  });
};
