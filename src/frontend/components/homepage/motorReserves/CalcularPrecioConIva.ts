export const calcularPrecioConIva = (precioSinIva: number, ivaPorcentaje: number = 0.21): { precioConIva: number; iva: number } => {
    // Calcular el IVA
    const iva = +(precioSinIva * ivaPorcentaje).toFixed(2);
    // Calcular el precio total con IVA
    const precioConIva = +(precioSinIva + iva).toFixed(2);
    
    return { precioConIva, iva };
  };
  