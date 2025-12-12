// src/pages/pago/imprimirDadesReserva.ts

type CarroLinea = {
  codigo: string;
  descripcion: string;
  cantidad: number;
  iva_percent: number;
  base: number;
  iva: number;
  total: number;
};

type CarroSnapshot = {
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

type TotalesFallback = {
  subtotal: number;
  iva_total: number;
  total: number;
};

function setText(id: string, value: string): void {
  const el = document.getElementById(id);
  if (el) el.textContent = value;
}

function eur2(n: number): string {
  return n.toFixed(2).replace('.', ',');
}

function splitDateTime(dt?: string): { date: string; time: string } {
  if (!dt) return { date: '', time: '' };
  const [d, t] = dt.split(' ');
  const [y, m, day] = d.split('-');
  const dateEs = `${day}/${m}/${y}`;
  const time = (t ?? '').slice(0, 5);
  return { date: dateEs, time };
}

function findLinea(lineas: CarroLinea[], pred: (l: CarroLinea) => boolean): CarroLinea | undefined {
  return lineas.find(pred);
}

export function imprimirDadesReserva(snapshot: CarroSnapshot, totals: TotalesFallback): void {
  const lineas = snapshot.lineas ?? [];

  // Reserva / Limpieza / Seguro (por prefijo para que sea robusto)
  const lineaReserva = findLinea(lineas, (l) => l.codigo.startsWith('RESERVA'));
  const lineaLimpieza = findLinea(lineas, (l) => l.codigo.startsWith('LIMPIEZA') || l.codigo.startsWith('LAVADO'));
  const lineaSeguro = findLinea(lineas, (l) => l.codigo.startsWith('SEGURO'));

  // Tipo reserva (usa descripción real)
  setText('tipoReserva', lineaReserva?.descripcion ?? snapshot.seleccion?.tipoReserva ?? '');

  // Fechas/horas (vienen en seleccion)
  const ent = splitDateTime(snapshot.seleccion?.fechaEntrada);
  const sal = splitDateTime(snapshot.seleccion?.fechaSalida);

  setText('fechaEntrada', ent.date);
  setText('horaEntrada', ent.time);
  setText('fechaSalida', sal.date);
  setText('horaSalida', sal.time);

  // Días
  setText('diasReserva', String(snapshot.diasReserva ?? ''));

  // Precios “sin IVA” (en tu tabla pones “Sin IVA”, así que usamos base)
  setText('precioReserva', eur2(lineaReserva?.base ?? 0));

  // Seguro
  if (lineaSeguro) {
    setText('seguroCancelacion', 'Contratado');
    setText('costeSeguro2', `${eur2(lineaSeguro.base)} € (sin IVA)`);
  } else {
    setText('seguroCancelacion', 'No');
    setText('costeSeguro2', `0,00 €`);
  }

  // Limpieza
  if (lineaLimpieza) {
    setText('tipoLimpieza2', lineaLimpieza.descripcion);
    setText('costeLimpieza', `${eur2(lineaLimpieza.base)} € (sin IVA)`);
  } else {
    setText('tipoLimpieza2', 'No');
    setText('costeLimpieza', `0,00 €`);
  }

  // Totales: preferimos snapshot.totales si existe, sino fallback del endpoint
  const sub = snapshot.totales?.subtotal_sin_iva ?? totals.subtotal;
  const iva = snapshot.totales?.iva_total ?? totals.iva_total;
  const tot = snapshot.totales?.total_con_iva ?? totals.total;

  setText('costeSubTotal', eur2(sub));
  setText('costeIva', eur2(iva));
  setText('costeTotal', eur2(tot));
  setText('costeTotal2', eur2(tot));
  setText('costeTotal3', eur2(tot)); // por si reactivas bizum
}
