// src/pages/home/motorReserves/carroCompraBackend.ts

import { API_URL } from '../../../../config/environment';
import { CotizarLinea, CotizarResponse } from './mostrarPreu';

interface CarritoDataSuccess {
    diasReserva: number;
    lineas: CotizarLinea[];
    subtotal: number;
    iva_total: number;
    total: number;
    hash: string;
}

function isCotizarLinea(x: unknown): x is CotizarLinea {
    if (!x || typeof x !== 'object') return false;
    const o = x as Record<string, unknown>;
    return (
        typeof o.codigo === 'string' &&
        typeof o.descripcion === 'string' &&
        typeof o.cantidad === 'number' &&
        typeof o.iva_percent === 'number' &&
        typeof o.base === 'number' &&
        typeof o.iva === 'number' &&
        typeof o.total === 'number'
    );
}

function getOrCreateSessionKey(): string {
    const key = 'carro_session';
    let s = localStorage.getItem(key);
    if (s && s.length >= 6) return s;

    s = Math.random().toString(36).slice(2, 10);
    localStorage.setItem(key, s);
    return s;
}

function parseDateRangeValue(
    value: string
): { start: string; end: string } | null {
    if (!value) return null;
    const parts = value.split(' to ');
    if (parts.length !== 2) return null;
    return { start: parts[0].trim(), end: parts[1].trim() };
}

function buildDateTime(dateYmd: string, hourHHmm: string): string {
    if (!dateYmd || !hourHHmm) return '';
    return `${dateYmd} ${hourHHmm}:00`;
}

function isSuccessEnvelope(
    x: unknown
): x is { status: 'success'; data: CarritoDataSuccess } {
    if (!x || typeof x !== 'object') return false;
    const o = x as Record<string, unknown>;
    if (o.status !== 'success' || !o.data || typeof o.data !== 'object') {
        return false;
    }
    const d = o.data as Record<string, unknown>;
    return (
        typeof d.diasReserva === 'number' &&
        Array.isArray(d.lineas) &&
        d.lineas.every(isCotizarLinea) &&
        typeof d.subtotal === 'number' &&
        typeof d.iva_total === 'number' &&
        typeof d.total === 'number'
    );
}

function isErrorEnvelope(
    x: unknown
): x is { status: 'error'; message: string; codigo?: string } {
    if (!x || typeof x !== 'object') return false;
    const o = x as Record<string, unknown>;
    return o.status === 'error' && typeof o.message === 'string';
}

export async function pressupostCarroBackend(): Promise<CotizarResponse> {
    const tipoReservaEl = document.getElementById(
        'tipo_reserva'
    ) as HTMLSelectElement | null;
    const limpiezaEl = document.getElementById(
        'limpieza'
    ) as HTMLSelectElement | null;

    const fechaEl = document.getElementById(
        'fecha_reserva'
    ) as HTMLInputElement | null;
    const horaEntradaEl = document.getElementById(
        'horaEntrada'
    ) as HTMLSelectElement | null;
    const horaSalidaEl = document.getElementById(
        'horaSalida'
    ) as HTMLSelectElement | null;

    const seguroSiEl = document.getElementById(
        'seguroSi'
    ) as HTMLInputElement | null;
    const seguroNoEl = document.getElementById(
        'seguroNo'
    ) as HTMLInputElement | null;

    if (
        !tipoReservaEl ||
        !limpiezaEl ||
        !fechaEl ||
        !horaEntradaEl ||
        !horaSalidaEl ||
        !seguroSiEl ||
        !seguroNoEl
    ) {
        return {
            ok: false,
            diasReserva: 0,
            lineas: [],
            subtotal: 0,
            iva_total: 0,
            total: 0,
            error: 'Faltan elementos del formulario',
        };
    }

    const rango = parseDateRangeValue(fechaEl.value);
    if (!rango) {
        return {
            ok: false,
            diasReserva: 0,
            lineas: [],
            subtotal: 0,
            iva_total: 0,
            total: 0,
            error: 'Selecciona fechas',
        };
    }

    const horaEntrada = horaEntradaEl.value;
    const horaSalida = horaSalidaEl.value;
    if (!horaEntrada || !horaSalida) {
        return {
            ok: false,
            diasReserva: 0,
            lineas: [],
            subtotal: 0,
            iva_total: 0,
            total: 0,
            error: 'Selecciona horas',
        };
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

    const resp = await fetch(`${API_URL}/carro-compra/post`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
    });

    let json: unknown;
    try {
        json = await resp.json();
    } catch {
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

    // Error (400/422/500): { status: 'error', message, codigo? }
    if (isErrorEnvelope(json)) {
        return {
            ok: false,
            diasReserva: 0,
            lineas: [],
            subtotal: 0,
            iva_total: 0,
            total: 0,
            error: json.message,
            ...(json.codigo ? { codigo: json.codigo } : {}),
        };
    }

    if (!isSuccessEnvelope(json)) {
        return {
            ok: false,
            diasReserva: 0,
            lineas: [],
            subtotal: 0,
            iva_total: 0,
            total: 0,
            error: resp.ok
                ? 'Respuesta inválida del servidor'
                : `HTTP ${resp.status} al cotizar`,
        };
    }

    return {
        ok: true,
        diasReserva: json.data.diasReserva,
        lineas: json.data.lineas,
        subtotal: json.data.subtotal,
        iva_total: json.data.iva_total,
        total: json.data.total,
        hash: json.data.hash,
    };
}
