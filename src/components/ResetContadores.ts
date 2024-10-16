export const resetContadores = () => {
    const precioTotalElement = document.getElementById('precio_total');
    const numDiasElement = document.getElementById('num_dias');
  
    if (precioTotalElement && numDiasElement) {
      precioTotalElement.textContent = '0.00'; // Restablecer el precio total
      numDiasElement.textContent = '0'; // Restablecer el número de días
    }
  };