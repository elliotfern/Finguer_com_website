export const resetContadores = () => {
    const precioTotalElement = document.getElementById('precio_total');
    const numDiasElement = document.getElementById('num_dias');
    const horaEntradaElement = document.getElementById('horaEntrada');
    const horaSalidaElement = document.getElementById('horaSalida');
    if (precioTotalElement && numDiasElement && horaEntradaElement && horaSalidaElement) {
        precioTotalElement.textContent = '0.00'; // Restablecer el precio total
        numDiasElement.textContent = '0'; // Restablecer el número de días
        horaEntradaElement.textContent = "";
        horaSalidaElement.textContent = "";
    }
};
