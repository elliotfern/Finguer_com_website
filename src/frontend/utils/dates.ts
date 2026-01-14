export function formatDates(fecha: string): string {
  const date = new Date(fecha);
  const dia = String(date.getDate()).padStart(2, '0'); // Día con dos dígitos
  const mes = String(date.getMonth() + 1).padStart(2, '0'); // Mes (1-12), ajustamos con +1 porque getMonth() retorna un valor entre 0 y 11
  const any = date.getFullYear(); // Año completo

  return `${dia}-${mes}-${any}`;
}

export function formatDatesForm(fecha: string | null | undefined): string {
  if (!fecha || fecha === '0000-00-00' || fecha === '1970-01-01') return '';

  const date = new Date(fecha);
  if (isNaN(date.getTime())) return '';

  const dia = String(date.getUTCDate()).padStart(2, '0');
  const mes = String(date.getUTCMonth() + 1).padStart(2, '0');
  const any = date.getUTCFullYear();

  return `${dia}/${mes}/${any}`;
}

export function formatDatesFormDateTime(fecha: string | null | undefined): string | null {
  if (!fecha || fecha === '0000-00-00' || fecha === '1970-01-01') return null;

  const date = new Date(fecha);
  if (isNaN(date.getTime())) return null;

  const dia = String(date.getUTCDate()).padStart(2, '0');
  const mes = String(date.getUTCMonth() + 1).padStart(2, '0');
  const any = date.getUTCFullYear();

  const hora = String(date.getUTCHours()).padStart(2, '0');
  const minutos = String(date.getUTCMinutes()).padStart(2, '0');
  const segundos = String(date.getUTCSeconds()).padStart(2, '0');

  return `${dia}/${mes}/${any} ${hora}:${minutos}:${segundos}`;
}

export function formatDateTime(dt: string | null | undefined): string {
  if (!dt) return '';

  // Acepta "YYYY-MM-DD HH:MM:SS" o ISO "YYYY-MM-DDTHH:MM:SS"
  const s = dt.replace('T', ' ').trim();

  const [datePart, timePart] = s.split(' ');
  if (!datePart) return dt;

  const [y, m, d] = datePart.split('-');
  if (!y || !m || !d) return dt;

  let hh = '00';
  let mm = '00';

  if (timePart) {
    const parts = timePart.split(':');
    hh = parts[0] ?? '00';
    mm = parts[1] ?? '00';
  }

  return `${d}/${m}/${y} ${hh}:${mm}`;
}