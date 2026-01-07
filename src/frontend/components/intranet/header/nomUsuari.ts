import { apiUrl } from '../../../config/globals';

interface MeResponse {
  status: 'success' | 'error';
  data?: {
    uuid: string;
    role: string;
    name: string;
  };
}

export async function nomUsuari() {
  const urlAjax = `${apiUrl}/intranet/users/get/?type=user`;

  try {
    const response = await fetch(urlAjax, {
      method: 'GET',
      credentials: 'include',
      headers: {
        Accept: 'application/json',
      },
    });

    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

   const json = (await response.json()) as MeResponse;

    const payload = json.data;
    const welcomeMessage = payload?.name ? `Benvingut, ${payload.name}` : 'Usuari no trobat';

    const userDiv = document.getElementById('userDiv');
    if (userDiv) {
      userDiv.textContent = welcomeMessage;
    }
  } catch (error) {
    console.error('Error:', error); // Manejo de errores
  }
}
