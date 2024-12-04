import { calcularTotalReserva } from './CalcularTotalReserva.js';
import { actualizarBotonPagar } from './ActualizarBotonPagar.js';
export const showPrice = () => {
    // Actualizar el estado del botón de pagar y calcular el precio total al cambiar el rango de fechas
    const fechaReserva = document.getElementById('fecha_reserva');
    if (fechaReserva) {
        // Cambiar el evento a apply.daterangepicker
        $('#fecha_reserva').on('apply.daterangepicker', function () {
            calcularTotalReserva(); // Calcular el costo total cuando se selecciona un rango de fechas
            actualizarBotonPagar(); // Actualizar el estado del botón de pagar
        });
    }
    // Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
    // Obtener los elementos por su ID
    const tipoReservaElement = document.getElementById('tipo_reserva');
    const limpiezaElement = document.getElementById('limpieza');
    // Obtener los elementos del seguro de cancelación
    const seguroSiElement = document.getElementById('seguroSi');
    const seguroNoElement = document.getElementById('seguroNo');
    // Obtener los elementos de la fecha de entrada y salida
    const horaEntradaElement = document.getElementById('horaEntrada');
    const horaSalidaElement = document.getElementById('horaSalida');
    // Verificar si los elementos existen
    if (tipoReservaElement && limpiezaElement && seguroSiElement && seguroNoElement && horaEntradaElement && horaSalidaElement) {
        // Agregar un event listener para cambios en el tipo de reserva
        tipoReservaElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
        // Agregar un event listener para cambios en la limpieza
        limpiezaElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
        // Agregar event listeners para el seguro de cancelación
        seguroSiElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
        seguroNoElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
        horaEntradaElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
        horaSalidaElement.addEventListener('change', function () {
            calcularTotalReserva();
            actualizarBotonPagar();
        });
    }
};
