export const calcularPrecioSinIva = (precioConIva: number, ivaPorcentaje: number): { precioSinIva: number; iva: number } => {
  const precioSinIva = +(precioConIva / (1 + ivaPorcentaje)).toFixed(2);
  const iva = +(precioConIva - precioSinIva).toFixed(2);
  return { precioSinIva, iva };
};
