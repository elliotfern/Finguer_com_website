import { obrirFinestra, tancarFinestra } from './finestraEmergent/finestraEmergent';
import { compatadorReservesPendents } from './taulaReservesPendents/comptadorReservesPendents';
import { carregarDadesTaulaReservesPendents } from './taulaReservesPendents/taulaReservesPendents';

// TIPADO (mismo shape que usa tu popup)
type DeviceInfo = {
  dispositiu?: string;
  navegador?: string;
  sistema_operatiu?: string;
  ip?: string;
};

type DeviceInfoInput = DeviceInfo[] | DeviceInfo | null;

const DEVICE_INFO_ENDPOINT = (id: string) => `${window.location.origin}/api/intranet/reserves/get/?type=reservaId&id=${encodeURIComponent(id)}`;

async function obtenirDeviceInfo(id: string): Promise<DeviceInfoInput> {
  try {
    const url = DEVICE_INFO_ENDPOINT(id);
    const res = await fetch(url, { headers: { Accept: 'application/json' } });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    const data = await res.json();

    // 👇 Normalizamos: puede venir como objeto o como array
    if (Array.isArray(data)) return data as DeviceInfo[];
    return data as DeviceInfo;
  } catch (e) {
    // Fallback suave: si falla la API, devolvemos null (el popup mostrará "-")
    console.warn('No se pudo obtener DeviceInfo desde la API:', e);
    return null;
  }
}

export const reservesPendents = () => {
  carregarDadesTaulaReservesPendents();
  compatadorReservesPendents();

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
