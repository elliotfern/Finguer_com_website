import { resetContadores } from "./ResetContadores.js";
export const parseDate = (dateString) => {
    const [day, month, year] = dateString.split('-').map(Number);
    return new Date(year, month - 1, day); // Los meses en JavaScript son 0-indexed
};
export const validarFechas = () => {
    const fechaReserva = document.getElementById('fecha_reserva');
    if (fechaReserva) {
        const fechas = fechaReserva.value.split(' - ');
        const fechaInicio = parseDate(fechas[0]); // Convierte la fecha de inicio
        const fechaFin = parseDate(fechas[1]); // Convierte la fecha de fin
        const fechaActual = new Date(); // Obtiene la fecha actual
        // Calcular la diferencia en horas considerando tanto la fecha como la hora actual
        const horasDiferencia = (fechaInicio.getTime() - fechaActual.getTime()) / (1000 * 60 * 60);
        // Si la fecha de inicio es anterior a la fecha actual o dentro de las 12 horas
        if (fechaInicio < fechaActual || horasDiferencia < 12) {
            $('#mensaje_error').text('Tu fecha de llegada al parking debe ser al menos 12 horas después de la hora actual.');
            resetContadores(); // Llamar a la función para restablecer contadores
            return false;
        }
        // Comprobar si fechaFin es el mismo día o anterior a fechaInicio
        const isSameOrBefore = (date1, date2) => {
            // Comparar solo la fecha (sin horas)
            return date1.getTime() <= date2.getTime();
        };
        // Usar la función para comprobar
        if (isSameOrBefore(fechaFin, fechaInicio)) {
            $('#mensaje_error').text('La fecha de fin debe ser posterior a la fecha de inicio.');
            resetContadores(); // Llamar a la función para restablecer contadores
            return false;
        }
        $('#mensaje_error').text(''); // Limpiar mensaje de error si las fechas son válidas
        return true;
    }
};
