import { validarFechas } from "./ValidarFechas.js";
import { calcularTotalDiasReserva } from "./CalcularTotalDiasReserva.js";
export const calcularTotalReserva = () => {
    let precioTotal = 0;
    let costeSeguro = 0;
    let diasReserva = 0;
    let costoReserva = 0;
    // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
    if (!validarFechas()) {
        return { precioTotal: 0, costeSeguro: 0 };
    }
    // Cálculo del precio total y número de días
    const tipoReservaElement = document.getElementById('tipo_reserva');
    if (tipoReservaElement) {
        const tipoReserva = tipoReservaElement.value;
        if (tipoReserva === 'finguer_class') {
            costoReserva += 10;
        }
        else if (tipoReserva === 'gold_finguer') {
            costoReserva += 25;
        }
    }
    precioTotal = costoReserva; // Empezamos con el costo de reserva
    const fechaReserva = document.getElementById('fecha_reserva');
    if (fechaReserva) {
        diasReserva = calcularTotalDiasReserva(fechaReserva);
    }
    precioTotal += diasReserva * 5; // Añadir costo por día
    const limpiezaElement = document.getElementById('limpieza');
    if (limpiezaElement) {
        const costoLimpieza = parseInt(limpiezaElement.value, 10) || 0; // Asegúrate de que sea un número
        precioTotal += costoLimpieza; // Añadir costo de limpieza al precio total
    }
    // Verificar si el cliente ha seleccionado el seguro de cancelación
    const seguroCancelacion = $('input[name="seguroCancelacion"]:checked').val();
    // Si el cliente ha seleccionado 'Sí' en el seguro de cancelación
    if (seguroCancelacion === '1') {
        costeSeguro = precioTotal * 0.3; // Calcular el 30% del precio total
        if (costeSeguro < 12) {
            costeSeguro = 12; // Si el 30% es menor a 12, el coste del seguro es 12 euros
        }
        precioTotal += costeSeguro; // Añadir el coste del seguro al precio total
    }
    // Actualización de los elementos DOM para mostrar el precio total y número de días
    const precioTotalElement = document.getElementById('precio_total');
    const numDiasElement = document.getElementById('num_dias');
    const totalElement = document.getElementById('total');
    const diasElement = document.getElementById('dias');
    if (precioTotalElement && numDiasElement && totalElement && diasElement) {
        precioTotalElement.textContent = precioTotal.toFixed(2); // Mostrar el precio total con 2 decimales
        numDiasElement.textContent = diasReserva.toString(); // Mostrar el número de días de la reserva
        // Mostrar los mensajes de precio y número de días
        totalElement.style.display = 'block';
        diasElement.style.display = 'block';
    }
    return { precioTotal, costeSeguro }; // Retornar al final de la función
};
