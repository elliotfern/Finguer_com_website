import { calcularTotalReserva } from "./CalcularTotalReserva.js";

export const sendForm = () => {
  // Redirigir al usuario a la página de pago al hacer clic en el botón de pagar
  // Obtener el elemento por su ID
  const pagarButton = document.getElementById('pagar') as HTMLButtonElement | null;

  // Verificar si el botón existe
  if (pagarButton) {
    pagarButton.addEventListener('click', function (event) {
      event.preventDefault(); //

      // Llamar a calcularTotal y desestructurar el resultado
      const { precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, diasReserva } = calcularTotalReserva();

      // Crear el formulario
      const form = document.createElement('form');
      form.action = '/pago/';
      form.method = 'post';

      // Función para crear y agregar inputs ocultos al formulario
      const addHiddenInput = (name: string, value: string) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.appendChild(input);
      };

      // Crear un objeto con los datos del formulario
      const formData: Record<string, string> = {
        tipoReserva: (document.getElementById('tipo_reserva') as HTMLInputElement | null)?.value || '',
        fechaEntrada: '',
        fechaSalida: '',
        limpieza: (document.getElementById('limpieza') as HTMLInputElement | null)?.value || '',
        horaEntrada: (document.getElementById('horaEntrada') as HTMLInputElement | null)?.value || '',
        horaSalida: (document.getElementById('horaSalida') as HTMLInputElement | null)?.value || '',
      };

      // Obtener las fechas del elemento de reserva
      const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;
      if (fechaReservaElement) {
        const fechas = fechaReservaElement.value.split(' - ');
        formData.fechaEntrada = fechas[0] || '';
        formData.fechaSalida = fechas[1] || '';
      }

      // Obtener el valor del seguro de cancelación (Sí o No)
      const seguroCancelacionSeleccionado = $('input[name="seguroCancelacion"]:checked').val();
      const seguroTexto = seguroCancelacionSeleccionado === '1' ? '1' : '2';

      // Agregar el seguro de cancelación al objeto formData
      formData.seguroCancelacion = seguroTexto;

      // Agregar el coste del seguro de cancelación al objeto formData
      addHiddenInput('costeSeguro', costeSeguro.toFixed(2));
      addHiddenInput('costeReserva', precioReserva.toFixed(2));
      addHiddenInput('costeLimpieza', costoLimpiezaSinIva.toFixed(2));
      addHiddenInput('costeSubTotal', precioSubtotal.toFixed(2));
      addHiddenInput('costeIva', costeIva.toFixed(2));
      addHiddenInput('costeTotal', precioTotal.toFixed(2));
      addHiddenInput('numDias', diasReserva.toString());

      // Agregar los campos ocultos al formulario
      for (const [name, value] of Object.entries(formData)) {
        addHiddenInput(name, value);
      }

      // Adjuntar el formulario al cuerpo del documento y enviarlo
      document.body.appendChild(form);
      form.submit();
    });
  }
};