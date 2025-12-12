// recuperarDadesCarritoCompra.ts
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

type CarroGetResponse = {
  ok: boolean;
  subtotal?: number;
  iva_total?: number;
  total?: number;
  snapshot?: CarroSnapshot | null;
  error?: string;
};

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
    const resp = await fetch(`/api/carro-compra/get/?session=${encodeURIComponent(sessionCode)}`);
    if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

    const data = (await resp.json()) as CarroGetResponse;
    if (!data.ok || !data.snapshot) throw new Error(data.error || 'Carrito no encontrado');

    // pintar
    imprimirDadesReserva(data.snapshot, {
      subtotal: data.subtotal ?? 0,
      iva_total: data.iva_total ?? 0,
      total: data.total ?? 0,
    });

    // devolver snapshot para el pago
    return data.snapshot;
  } catch (e) {
    console.error(e);
    mostrarError();
    return null;
  }
}
