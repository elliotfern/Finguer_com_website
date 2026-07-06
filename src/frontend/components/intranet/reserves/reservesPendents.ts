import { API_URL } from '../../../config/environment';
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
    const keys: (keyof DeviceInfo)[] = [
        'dispositiu',
        'navegador',
        'sistema_operatiu',
        'ip',
    ];
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

const DEVICE_INFO_ENDPOINT = (id: string) =>
    `${API_URL}/intranet/reserves/get?type=reservaId&id=${encodeURIComponent(id)}`;

async function obtenirDeviceInfo(id: string): Promise<DeviceInfoInput> {
    try {
        const url = DEVICE_INFO_ENDPOINT(id);
        const res = await fetch(url, {
            headers: { Accept: 'application/json' },
        });
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

// ✅ nuevo: recordar filtros actuales para poder refrescar
let lastEstatParking = '';
let lastTipo: string | undefined;

// ✅ nuevo: listener de refresco solo 1 vez
let reservaChangedBound = false;

export const reserves = (estatParking: string, tipo?: string) => {
    // ✅ guardar últimos filtros (cada vez que llamas reserves, se actualizan)
    lastEstatParking = estatParking;
    lastTipo = tipo;

    carregarDadesTaulaReserves(estatParking, tipo);

    // ✅ enganchar refresco 1 sola vez
    if (!reservaChangedBound) {
        reservaChangedBound = true;

        window.addEventListener('reserva:changed', async () => {
            try {
                await carregarDadesTaulaReserves(lastEstatParking, lastTipo);
            } catch (e) {
                console.error(
                    'Error refrescando tabla tras cambio de reserva:',
                    e
                );
            }
        });
    }

    if (reservesClickBound) return;
    reservesClickBound = true;

    document.addEventListener('click', async (event: MouseEvent) => {
        const target = event.target as HTMLElement | null;
        if (!target) return;

        // ✅ Mejor con closest: funciona si clicas en un icono dentro del botón, etc.
        const openBtn = target.closest(
            '.obrir-finestra-btn'
        ) as HTMLElement | null;
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
