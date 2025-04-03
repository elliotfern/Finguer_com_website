import { PaymentData } from '../../types/interfaces';

interface ApiResponse {
  status: string;
  message: string;
  idCliente?: string;
  data?: string;
  errors: string;
}

interface ReservaData {
  reservaId: number;
  fecha: string;
  total: number;
}

export const creacioReserva = async (clientId: number, idReserva: string, dades: PaymentData): Promise<{ status: string; message: string; data?: ReservaData } | undefined> => {
  const url = `${window.location.origin}/api/alta-reserva`;

  // Obtener los valores de los campos del formulario
  const vehiculo = (document.getElementById('vehiculo') as HTMLInputElement).value;
  const matricula = (document.getElementById('matricula') as HTMLInputElement).value;
  const vuelo = (document.getElementById('vuelo') as HTMLInputElement).value;
  const numeroPersonas = (document.getElementById('numero_personas') as HTMLInputElement).value;

  const dataString = dades;
  if (dataString) {
    const dades = dataString;

    // Construir el objeto formData directamente desde dades
    const formData = {
      idClient: clientId,
      idReserva: idReserva,
      tipo: dades.tipoReserva,
      horaEntrada: dades.horaEntrada,
      diaEntrada: dades.fechaEntrada,
      horaSalida: dades.horaSalida,
      diaSalida: dades.fechaSalida,
      vehiculo: vehiculo,
      matricula: matricula,
      vuelo: vuelo,
      numeroPersonas: numeroPersonas,
      limpieza: dades.limpieza,
      processed: '0',
      cancelacion: dades.costeSeguro,
      costeSeguro: dades.costeSeguro,
      costeReserva: dades.precioReserva,
      costeLimpieza: dades.costoLimpiezaSinIva,
      costeSubTotal: dades.precioSubtotal,
      costeIva: dades.costeIva,
      costeTotal: dades.precioTotal,
      checkIn: '5',
    };

    try {
      // Enviar los datos usando POST
      const response = await fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(formData),
      });

      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }

      const text = await response.text(); // Leer la respuesta como texto
      let datos: ApiResponse;

      try {
        datos = JSON.parse(text); // Intentar convertir a JSON
      } catch (e) {
        console.error('Error al analizar JSON:', e);
        throw new Error('Error al analizar JSON');
      }

      // Limpiar errores previos
      document.querySelectorAll('.invalid-feedback').forEach((el) => (el.textContent = ''));
      document.querySelectorAll('.form-control').forEach((el) => el.classList.remove('is-invalid'));

      if (datos.status === 'error' && datos.errors) {
        // Mostrar errores en cada campo
        for (const [field, message] of Object.entries(datos.errors)) {
          const errorDiv = document.getElementById(`error-${field}`);
          const inputField = document.getElementById(field);

          if (errorDiv && inputField) {
            errorDiv.textContent = typeof message === 'string' ? message : '';
            inputField.classList.add('is-invalid');
          }
        }
        return { status: 'error', message: 'Errores en los datos enviados.' };
      }

      // Procesar la respuesta
      const messageError = document.querySelector('#messageErr') as HTMLElement;
      const messageOk = document.querySelector('#messageOk') as HTMLElement;

      if (datos.status === 'success') {
        if (messageOk && messageError) {
          messageError.style.display = 'none';
          messageOk.style.display = 'block';

          return {
            status: datos.status,
            message: datos.message,
          };
        }
      } else {
        if (messageError && messageOk) {
          messageError.style.display = 'block';
          messageOk.style.display = 'none';

          return { status: 'error', message: 'Error' };
        }
      }
    } catch (error) {
      console.error('Error en la solicitud:', error);
    }
  } else {
    console.error('No se encontró "paymentData" en localStorage');
    return { status: 'error', message: 'No hay datos en localStorage' };
  }
};
