export const seleccionaHoraTipoReserva = () => {
    // Obtener los elementos del DOM
    const tipoReservaSelect = document.getElementById('tipo_reserva');
    const horaEntradaSelect = document.getElementById('horaEntrada');
    const horaSalidaSelect = document.getElementById('horaSalida');
    // Definir las opciones de horas
    const horasFinguerClass = [
        '05:00', '05:30', '06:00', '06:30', '07:00', '07:30', '08:00', '08:30',
        '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30',
        '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30',
        '17:00', '17:30', '18:00', '18:30', '19:00', '19:30', '20:00', '20:30',
        '21:00', '21:30', '22:00', '22:30', '23:00', '23:30'
    ];
    const horasGoldFinguer = [
        '08:00', '08:30', '09:00', '09:30', '10:00', '10:30',
        '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
        '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30',
        '19:00', '19:30', '20:00', '20:30', '21:00'
    ];
    // Función para llenar las horas en los selects
    const llenarHoras = (tipo) => {
        // Guardar las selecciones actuales antes de vaciar los selects
        const horaEntradaSeleccionada = horaEntradaSelect.value;
        const horaSalidaSeleccionada = horaSalidaSelect.value;
        // Limpiar las opciones actuales de los selects
        horaEntradaSelect.innerHTML = '<option selected value="">Selecciona una hora:</option>';
        horaSalidaSelect.innerHTML = '<option selected value="">Selecciona una hora:</option>';
        // Seleccionar las horas dependiendo del tipo de reserva
        const horas = tipo === 'gold_finguer' ? horasGoldFinguer : horasFinguerClass;
        // Llenar las opciones de horaEntrada
        horas.forEach((hora) => {
            const option = document.createElement('option');
            option.value = hora;
            option.textContent = hora;
            horaEntradaSelect.appendChild(option);
        });
        // Llenar las opciones de horaSalida
        horas.forEach((hora) => {
            const option = document.createElement('option');
            option.value = hora;
            option.textContent = hora;
            horaSalidaSelect.appendChild(option);
        });
        // Restaurar la selección anterior si aún es válida
        if (horas.includes(horaEntradaSeleccionada)) {
            horaEntradaSelect.value = horaEntradaSeleccionada;
        }
        if (horas.includes(horaSalidaSeleccionada)) {
            horaSalidaSelect.value = horaSalidaSeleccionada;
        }
    };
    // Inicializar las horas según el valor inicial del select
    llenarHoras(tipoReservaSelect.value);
    // Cambiar las opciones al seleccionar otro tipo de reserva
    tipoReservaSelect.addEventListener('change', (event) => {
        const selectedTipo = event.target.value;
        llenarHoras(selectedTipo);
    });
};
