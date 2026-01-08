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
