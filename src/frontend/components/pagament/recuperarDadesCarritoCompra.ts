// recuperarDadesCarritoCompra.ts
import { API_URL } from '../../config/environment';
import { imprimirDadesReserva } from './imprimirDadesReserva';

export type CarroLinea = {
    codigo: string;
    descripcion: string;
    cantidad: number;
    iva_percent: number;
    base: number;
    iva: number;
    total: number;
};

export type CarroSnapshot = {
    diasReserva?: number;
    seleccion?: {
        tipoReserva?: string;
        limpieza?: string;
        seguroCancelacion?: number;
        fechaEntrada?: string;
        fechaSalida?: string;
    };
    lineas?: CarroLinea[];
    totales?: {
        subtotal_sin_iva: number;
        iva_total: number;
        total_con_iva: number;
    };
};

type CarroGetSuccessData = {
    session: string;
    subtotal: number;
    iva_total: number;
    total: number;
    hash: string;
    updated_at: string | null;
    snapshot: CarroSnapshot;
};

type CarroGetResponse =
    | { status: 'success'; data: CarroGetSuccessData }
    | { status: 'error'; message: string };

function getSessionFromUrl(): string | null {
    const parts = window.location.pathname.split('/').filter(Boolean);
    return parts.length ? decodeURIComponent(parts[parts.length - 1]) : null;
}

function mostrarError(): void {
    const ok = document.getElementById('pantallaPagament');
    const err = document.getElementById('pantallaPagamentError');
    if (ok) ok.style.display = 'none';
    if (err) err.style.display = 'block';
}

export async function recuperarCarroCompra(): Promise<CarroSnapshot | null> {
    const sessionCode = getSessionFromUrl();
    if (!sessionCode) {
        mostrarError();
        return null;
    }

    try {
        const resp = await fetch(
            `${API_URL}/carro-compra/get?session=${encodeURIComponent(sessionCode)}`
        );

        const data = (await resp.json()) as CarroGetResponse;

        if (data.status !== 'success') {
            throw new Error(data.message || 'Carrito no encontrado');
        }

        // pintar
        imprimirDadesReserva(data.data.snapshot, {
            subtotal: data.data.subtotal,
            iva_total: data.data.iva_total,
            total: data.data.total,
        });

        // devolver snapshot para el pago
        return data.data.snapshot;
    } catch (e) {
        console.error(e);
        mostrarError();
        return null;
    }
}
