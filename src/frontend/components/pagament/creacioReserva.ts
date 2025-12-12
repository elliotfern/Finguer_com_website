interface ApiResponse {
  status: string;
  message: string;
  errors?: Record<string, string>;
}

function getSessionFromUrl(): string | null {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const last = parts[parts.length - 1];
  return last ? decodeURIComponent(last) : null;
}

export const creacioReserva = async (clientId: number, idReserva: string): Promise<{ status: string; message: string } | undefined> => {
  const url = `${window.location.origin}/api/alta-reserva`;

  const session = getSessionFromUrl();
  if (!session) {
    return { status: 'error', message: 'No se pudo determinar la sesión del carrito.' };
  }

  // Campos del formulario (usuario)
  const vehiculo = (document.getElementById('vehiculo') as HTMLInputElement | null)?.value || '';
  const matricula = (document.getElementById('matricula') as HTMLInputElement | null)?.value || '';
  const vuelo = (document.getElementById('vuelo') as HTMLInputElement | null)?.value || '';
  const numeroPersonas = (document.getElementById('numero_personas') as HTMLInputElement | null)?.value || '';

  // Enviamos SOLO lo necesario. El backend calcula/importa todo desde carro_compra por session.
  const formData = {
    idClient: clientId,
    idReserva: idReserva, // tu order Redsys (mdHis)
    session: session, // <- clave para leer carro_compra
    vehiculo,
    matricula,
    vuelo,
    numeroPersonas,
    processed: '0',
    checkIn: '5',
  };

  try {
    const response = await fetch(url, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(formData),
    });

    if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);

    const text = await response.text();
    let datos: ApiResponse;

    try {
      datos = JSON.parse(text) as ApiResponse;
    } catch (e) {
      console.error('Error al analizar JSON:', e, text);
      return { status: 'error', message: 'Respuesta inválida del servidor.' };
    }

    // Limpiar errores previos
    document.querySelectorAll('.invalid-feedback').forEach((el) => (el.textContent = ''));
    document.querySelectorAll('.form-control').forEach((el) => el.classList.remove('is-invalid'));

    if (datos.status === 'error' && datos.errors) {
      for (const [field, message] of Object.entries(datos.errors)) {
        const errorDiv = document.getElementById(`error-${field}`);
        const inputField = document.getElementById(field);
        if (errorDiv && inputField) {
          errorDiv.textContent = message;
          inputField.classList.add('is-invalid');
        }
      }
      return { status: 'error', message: 'Errores en los datos enviados.' };
    }

    const messageError = document.querySelector('#messageErr') as HTMLElement | null;
    const messageOk = document.querySelector('#messageOk') as HTMLElement | null;

    if (datos.status === 'success') {
      if (messageOk) messageOk.style.display = 'block';
      if (messageError) messageError.style.display = 'none';
      return { status: 'success', message: datos.message };
    }

    if (messageError) messageError.style.display = 'block';
    if (messageOk) messageOk.style.display = 'none';
    return { status: 'error', message: datos.message || 'Error' };
  } catch (error) {
    console.error('Error en la solicitud:', error);
    return { status: 'error', message: 'Error en la solicitud.' };
  }
};
