export const daterangepicker = () => {
    const startDate = new Date();
    startDate.setDate(startDate.getDate() + 2); // Fecha de inicio + 2 días
    const endDate = new Date(startDate); // Fecha de fin igual a la de inicio
    $('#fecha_reserva').daterangepicker({
        autoApply: true,
        endDate: formatDate(endDate),
        minDate: formatDate(new Date(startDate.getFullYear(), startDate.getMonth(), startDate.getDate())),
        locale: {
            format: 'DD-MM-YYYY',
            firstDay: 1,
            cancelLabel: 'Cancelar',
            applyLabel: 'Aplicar',
            daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sá'],
            monthNames: [
                // Nombres de los meses
                'Enero',
                'Febrero',
                'Marzo',
                'Abril',
                'Mayo',
                'Junio',
                'Julio',
                'Agosto',
                'Septiembre',
                'Octubre',
                'Noviembre',
                'Diciembre',
            ],
        },
    });
};
// Función para formatear una fecha como DD-MM-YYYY
const formatDate = (date) => {
    const day = String(date.getDate()).padStart(2, '0');
    const month = String(date.getMonth() + 1).padStart(2, '0'); // Los meses comienzan desde 0
    const year = date.getFullYear();
    return `${day}-${month}-${year}`;
};
