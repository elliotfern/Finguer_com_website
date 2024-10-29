import { validarFechas } from './ValidarFechas.js';
import { calcularTotalDiasReserva } from './CalcularTotalDiasReserva.js';
import { calcularPrecioSinIva } from './CalcularPrecioSinIva.js';
import { calcularPrecioConIva } from './CalcularPrecioConIva.js';
export const calcularTotalReserva = () => {
    let precioTotal = 0;
    let precioSubtotal = 0;
    let costeIva = 0;
    let costeSeguro = 0;
    let diasReserva = 0;
    let costoReserva = 0;
    const costoDia = 5;
    const porcentajeIva = 0.21;
    const costeReservaFinguerClass = 10.01;
    const costeReservaGoldClass = 25;
    const costeReservaFinguerClassSinIva = calcularPrecioSinIva(costeReservaFinguerClass, porcentajeIva).precioSinIva;
    const costeReservaGoldClassSinIva = calcularPrecioSinIva(costeReservaGoldClass, porcentajeIva).precioSinIva;
    const costoDiaSinIva = calcularPrecioSinIva(costoDia, porcentajeIva).precioSinIva;
    // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
    if (!validarFechas()) {
        return { precioTotal: 0, costeSeguro: 0, precioSinLimpieza: 0 };
    }
    // Cálculo del precio total y número de días
    const tipoReservaElement = document.getElementById('tipo_reserva');
    if (tipoReservaElement) {
        const tipoReserva = tipoReservaElement.value;
        if (tipoReserva === 'finguer_class') {
            costoReserva += costeReservaFinguerClassSinIva;
        }
        else if (tipoReserva === 'gold_finguer') {
            costoReserva += costeReservaGoldClassSinIva;
        }
    }
    precioSubtotal = costoReserva;
    const fechaReserva = document.getElementById('fecha_reserva');
    if (fechaReserva) {
        diasReserva = calcularTotalDiasReserva(fechaReserva);
    }
    precioSubtotal += diasReserva * costoDiaSinIva; // Añadir costo por día
    const precioSinLimpieza = precioSubtotal;
    const limpiezaElement = document.getElementById('limpieza');
    if (limpiezaElement) {
        const costoLimpieza = parseInt(limpiezaElement.value, 10) || 0; // Asegúrate de que sea un número
        let costoLimpiezaSinIva = 0;
        if (costoLimpieza === 15) {
            costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
        }
        else if (costoLimpieza === 25) {
            costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
        }
        else if (costoLimpieza === 55) {
            costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
        }
        precioSubtotal += costoLimpiezaSinIva; // Añadir costo de limpieza al precio total
    }
    // Verificar si el cliente ha seleccionado el seguro de cancelación
    const seguroCancelacion = $('input[name="seguroCancelacion"]:checked').val();
    // Si el cliente ha seleccionado 'Sí' en el seguro de cancelación
    if (seguroCancelacion === '1') {
        costeSeguro = precioSubtotal * 0.3; // Calcular el 30% del precio total
        if (costeSeguro < 12) {
            costeSeguro = 12; // Si el 30% es menor a 12, el coste del seguro es 12 euros
        }
        precioSubtotal += costeSeguro; // Añadir el coste del seguro al precio total
    }
    // Calcular IVA (+21%)
    costeIva = calcularPrecioConIva(precioSubtotal, porcentajeIva).iva;
    // 5 - Calcula el Importe total iva incluido
    precioTotal = calcularPrecioConIva(precioSubtotal, porcentajeIva).precioConIva;
    // Actualización de los elementos DOM para mostrar el precio total y número de días
    const totalElement = document.getElementById('total');
    const precioSubTotalElement = document.getElementById('subTotal');
    const ivaElement = document.getElementById('precio_iva');
    const numDiasElement = document.getElementById('num_dias');
    const diasElement = document.getElementById('dias');
    if (numDiasElement && totalElement && diasElement && precioSubTotalElement && ivaElement) {
        precioSubTotalElement.innerHTML = `Subtotal: ${precioSubtotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
        ivaElement.innerHTML = `IVA (21%): ${costeIva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
        totalElement.innerHTML = `Precio Total: ${precioTotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € IVA incluido`;
        numDiasElement.innerHTML = diasReserva.toString();
        // Mostrar los mensajes de precio y número de días
        precioSubTotalElement.style.display = 'block';
        totalElement.style.display = 'block';
        diasElement.style.display = 'block';
        ivaElement.style.display = 'block';
    }
    return { precioTotal, costeSeguro, precioSinLimpieza };
};
