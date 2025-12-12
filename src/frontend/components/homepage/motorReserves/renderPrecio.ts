// renderPrecio.ts

export type CotizarLinea = {
  codigo: string;
  descripcion: string;
  cantidad: number;
  iva_percent: number;
  base: number;
  iva: number;
  total: number;
};

export type CotizarResponse = {
  ok: boolean;
  diasReserva: number;
  lineas: CotizarLinea[];
  subtotal: number;
  iva_total: number;
  total: number;
  hash?: string;
  error?: string;
};

function setText(id: string, text: string, show = true): void {
  const el = document.getElementById(id);
  if (!el) return;
  el.textContent = text;
  (el as HTMLElement).style.display = show ? 'block' : 'none';
}

function eur(n: number): string {
  return `${n.toFixed(2)} €`;
}

function findLinea(lineas: CotizarLinea[], codigos: readonly string[]): CotizarLinea | undefined {
  return lineas.find((l) => codigos.includes(l.codigo));
}

export function renderCotizacion(data: CotizarResponse): void {
  setText('resumenReserva', `Duración de la reserva: ${data.diasReserva} días`, true);

  const lineas = data.lineas;

  const tarifa = findLinea(lineas, ['RESERVA_FINGUER', 'RESERVA_FINGUER_GOLD'] as const);
  const limpieza = findLinea(lineas, ['LIMPIEZA_EXT', 'LIMPIEZA_EXT_INT', 'LIMPIEZA_PRO'] as const);
  const seguro = findLinea(lineas, ['SEGURO_CANCELACION'] as const);

  if (tarifa) setText('costeReserva', `Reserva: ${eur(tarifa.base)} (sin IVA)`, true);
  else setText('costeReserva', '', false);

  if (seguro) setText('costeSeguro', `Seguro: ${eur(seguro.base)} (sin IVA)`, true);
  else setText('costeSeguro', `Seguro: No contratado`, true);

  if (limpieza) setText('costeLimpieza', `Limpieza: ${eur(limpieza.base)} (sin IVA)`, true);
  else setText('costeLimpieza', `Limpieza: No contratada`, true);

  setText('subTotal', `Subtotal: ${eur(data.subtotal)} (sin IVA)`, true);
  setText('precio_iva', `IVA: ${eur(data.iva_total)}`, true);
  setText('total', `Total: ${eur(data.total)}`, true);
}
