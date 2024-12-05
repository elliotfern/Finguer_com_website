// enviarConformacioReserva.ts

export const enviarConfirmacioReserva = async (id: string): Promise<void> => {
  const url = `${window.location.origin}/api/intranet/email/get/?type=emailConfirmacioReserva&id=${id}`;

  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`HTTP error! status: ${response.status}`);
  }

  const text = await response.text(); // Leer la respuesta como texto

  let datos;

  try {
    datos = JSON.parse(text); // Intentar convertir a JSON
  } catch (e) {
    console.error('Error al analizar JSON:', e);
    throw new Error('Error al analizar JSON');
  }

  if (datos.message === 'success') {
    const boton = document.getElementById('enlace1');

    if (boton) {
      boton.textContent = 'Email enviat!';

      // Cambiar el estilo del botón (puedes agregar una clase CSS como ejemplo)
      boton.classList.add('btn-success'); // Cambiar el color del botón
      boton.classList.remove('btn-secondary'); // Eliminar el estilo original

      // Desactivar el cursor para reflejar el estado desactivado visualmente
      boton.style.cursor = 'not-allowed';
      boton.style.opacity = '0.5';
    }
  } else {
    // Aquí podrías manejar otros casos si la respuesta no es "success"
    console.log('Error al enviar el email');
  }
};
