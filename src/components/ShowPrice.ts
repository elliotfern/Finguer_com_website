import { calcularTotalReserva } from './CalcularTotalReserva.js';
import { actualizarBotonPagar } from './ActualizarBotonPagar.js';

export const showPrice = () => {
  // Actualizar el estado del botón de pagar y calcular el precio total al cambiar el rango de fechas
  const fechaReserva = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  if (fechaReserva) {
    // Cambiar el evento a apply.daterangepicker
    $('#fecha_reserva').on('apply.daterangepicker', function () {
      calcularTotalReserva(); // Calcular el costo total cuando se selecciona un rango de fechas
      actualizarBotonPagar(); // Actualizar el estado del botón de pagar
    });
  }

  // Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
  // Obtener los elementos por su ID
  const tipoReservaElement = document.getElementById('tipo_reserva') as HTMLSelectElement | null;
  const limpiezaElement = document.getElementById('limpieza') as HTMLInputElement | null;

  // Obtener los elementos del seguro de cancelación
  const seguroSiElement = document.getElementById('seguroSi') as HTMLInputElement | null;
  const seguroNoElement = document.getElementById('seguroNo') as HTMLInputElement | null;

  // Verificar si los elementos existen
  if (tipoReservaElement && limpiezaElement && seguroSiElement && seguroNoElement) {
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
  }
};
