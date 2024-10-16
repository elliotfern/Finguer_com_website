import { validarFechas, parseDate } from './ValidarFechas.js';
export const calcularTotalSinLimpieza = () => {
    // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
    if (!validarFechas()) {
        return 0;
    }
    // Cálculo del precio total sin incluir el costo de la limpieza
    const tipoReserva = $('#tipo_reserva').val();
    let costoReserva = 0;
    if (tipoReserva === 'finguer_class') {
        costoReserva += 10;
    }
    else if (tipoReserva === 'gold_finguer') {
        costoReserva += 25;
    }
    const fechaReserva = document.getElementById('fecha_reserva');
    if (fechaReserva) {
        const fechas = fechaReserva.value.split(' - ');
        const fechaInicio = parseDate(fechas[0]); // Convierte la fecha de inicio
        const fechaFin = parseDate(fechas[1]); // Convierte la fecha de fin
        // Calcular la diferencia en días
        const calcularDiasReserva = (fechaInicio, fechaFin) => {
            // Obtener la diferencia en milisegundos
            const diferenciaEnMilisegundos = fechaFin.getTime() - fechaInicio.getTime();
            // Convertir milisegundos a días
            const diferenciaEnDias = Math.ceil(diferenciaEnMilisegundos / (1000 * 60 * 60 * 24));
            // Sumar 1 para incluir el día de inicio
            return diferenciaEnDias + 1;
        };
        // Usar la función para calcular los días de reserva
        const diasReserva = calcularDiasReserva(fechaInicio, fechaFin);
        costoReserva += diasReserva * 5;
    }
    return costoReserva;
};
