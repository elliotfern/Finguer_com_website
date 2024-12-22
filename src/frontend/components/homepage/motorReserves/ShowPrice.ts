import { calcularTotalReserva } from './CalcularTotalReserva';
import { actualizarBotonPagar } from './ActualizarBotonPagar';

export const showPrice = () => {
  // Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
  // Obtener los elementos por su ID
  const tipoReservaElement = document.getElementById('tipo_reserva') as HTMLSelectElement | null;
  const limpiezaElement = document.getElementById('limpieza') as HTMLInputElement | null;

  // Obtener los elementos del seguro de cancelación
  const seguroSiElement = document.getElementById('seguroSi') as HTMLInputElement | null;
  const seguroNoElement = document.getElementById('seguroNo') as HTMLInputElement | null;

  // Obtener los elementos de la fecha de entrada y salida
  const horaEntradaElement = document.getElementById('horaEntrada') as HTMLInputElement | null;
  const horaSalidaElement = document.getElementById('horaSalida') as HTMLInputElement | null;

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
