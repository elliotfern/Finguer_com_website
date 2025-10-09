export const calcularPrecioConIva = (precioSinIva: number, ivaPorcentaje: number = 0.21): { precioConIva: number; iva: number } => {
  // Calcular IVA y redondear a 2 decimales
  const iva = +(precioSinIva * ivaPorcentaje).toFixed(2);
  // Calcular precio con IVA ya redondeado
  const precioConIva = +(precioSinIva + iva).toFixed(2);

  return { precioConIva, iva };
};
