import { apiUrl } from '../../../config/globals';

export const logout = async (event: MouseEvent) => {
  // Evita que el enlace realice la acción predeterminada (redirigir a otra página)
  event.preventDefault();

  const urlAjax = `${apiUrl}/intranet/users/get/?type=deleteCookies`;

  try {
    const response = await fetch(urlAjax);
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    await response.json(); // Parsea la respuesta JSON
    window.location.href = '/control/login'; // Redirige al login
  } catch (error) {
    console.error('Error:', error); // Manejo de errores
  }
};
