import { creacioReserva } from './creacioReserva';

export const creacioDadesUsuaris = async (idReserva: string): Promise<{ status: string; message: string }> => {
  const url = `${window.location.origin}/api/alta-client`;
  const idReserva2 = idReserva;

  // Obtener los datos del formulario
  const formData = {
    nombre: (document.getElementById('nombre') as HTMLInputElement)?.value || '',
    telefono: (document.getElementById('telefono') as HTMLInputElement)?.value || '',
    email: (document.getElementById('email') as HTMLInputElement)?.value || '',
    empresa: (document.getElementById('empresa') as HTMLInputElement)?.value || '',
    nif: (document.getElementById('nif') as HTMLInputElement)?.value || '',
    direccion: (document.getElementById('direccion') as HTMLInputElement)?.value || '',
    ciudad: (document.getElementById('ciudad') as HTMLInputElement)?.value || '',
    codigo_postal: (document.getElementById('codigo_postal') as HTMLInputElement)?.value || '',
    pais: (document.getElementById('pais') as HTMLInputElement)?.value || '',
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

    const data = await response.json(); // Leer la respuesta como JSON

    // Limpiar errores previos
    document.querySelectorAll('.invalid-feedback').forEach((el) => (el.textContent = ''));
    document.querySelectorAll('.form-control').forEach((el) => el.classList.remove('is-invalid'));

    if (data.status === 'error' && data.errors) {
      // Mostrar errores en cada campo
      for (const [field, message] of Object.entries(data.errors)) {
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
    if (data.status === 'success') {
      const clientId = data.idCliente;

      // Llamar a la función creacioReserva
      const reservaResponse = await creacioReserva(clientId, idReserva2);
      if (reservaResponse?.status === 'success') {
        return { status: 'success', message: 'Reserva creada correctamente' };
      } else {
        return { status: 'error', message: 'No se ha creado la reserva' };
      }
    } else {
      //console.error('Error en la creación del cliente:', data.message);
      return { status: 'error', message: `Error en la solicitud` };
    }
  } catch (error) {
    //console.error('Error en la solicitud:', error);
    return { status: 'error', message: `Error en la solicitud ${error}` };
  }
};
