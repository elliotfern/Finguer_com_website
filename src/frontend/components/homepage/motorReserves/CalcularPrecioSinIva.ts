export const calcularPrecioSinIva = (precioConIva: number, ivaPorcentaje: number): { precioSinIva: number; iva: number } => {
  const precioFinal = +precioConIva.toFixed(2); // redondeamos la entrada
  const precioSinIva = +(precioFinal / (1 + ivaPorcentaje)).toFixed(2);
  const iva = +(precioFinal - precioSinIva).toFixed(2);
  return { precioSinIva, iva };
};
