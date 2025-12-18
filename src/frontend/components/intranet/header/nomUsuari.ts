import { apiUrl } from '../../../config/globals';

export async function nomUsuari() {
  const urlAjax = `${apiUrl}/intranet/users/get/?type=user`;

  try {
    const response = await fetch(urlAjax);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    const data = await response.json(); // Parsea la respuesta JSON
    const welcomeMessage = data.nombre ? `Benvingut, ${data.nombre}` : 'Usuari no trobat';
    const userDiv = document.getElementById('userDiv');
    if (userDiv) {
      userDiv.innerHTML = welcomeMessage; // Actualiza el contenido de #userDiv
    }
  } catch (error) {
    console.error('Error:', error); // Manejo de errores
  }
}
