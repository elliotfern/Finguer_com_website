export const parseData = (fechaStr: string): Date => {
  const limpio = fechaStr.trim();

  // Caso 1: formato dd/mm/yyyy
  if (limpio.includes('/')) {
    const [dia, mes, anio] = limpio.split('/').map((n) => parseInt(n, 10));
    return new Date(anio, mes - 1, dia);
  }

  // Caso 2: formato dd-mm-yyyy
  if (limpio.includes('-') && limpio.split('-')[0].length === 2) {
    const [dia, mes, anio] = limpio.split('-').map((n) => parseInt(n, 10));
    return new Date(anio, mes - 1, dia);
  }

  // Caso 3: formato ISO (yyyy-mm-dd) -> Safari lo acepta nativamente
  return new Date(limpio);
};
