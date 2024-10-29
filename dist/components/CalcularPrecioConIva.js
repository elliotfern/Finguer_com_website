export const calcularPrecioConIva = (precioSinIva, ivaPorcentaje = 0.21) => {
    // Calcular el IVA
    const iva = +(precioSinIva * ivaPorcentaje).toFixed(2);
    // Calcular el precio total con IVA
    const precioConIva = +(precioSinIva + iva).toFixed(2);
    return { precioConIva, iva };
};
