import { fetchData } from '../../services/api/api';

interface ApiResponse {
  status: string;
  message: string;
}

export const areaClientLogin = () => {
  document.addEventListener('DOMContentLoaded', () => {
    login();
  });
};

const login = () => {
  const loginButton = document.getElementById('btnLogin') as HTMLButtonElement;
  const loginMessageOk = document.getElementById('loginMessageOk') as HTMLElement;
  const loginMessageErr = document.getElementById('loginMessageErr') as HTMLElement;

  if (loginButton) {
    loginButton.addEventListener('click', async (event) => {
      event.preventDefault();

      const email = (document.getElementById('email') as HTMLInputElement).value;

      try {
        // Usamos fetchData para hacer la solicitud
        const response = await fetchData<ApiResponse, { email: string }>(`https://${window.location.hostname}/api/area-client/login`, 'POST', { email: email });

        if (response) {
          if (response.status === 'success') {
            loginMessageOk.innerHTML = response.message;
            loginMessageOk.style.display = 'block';
            loginMessageErr.style.display = 'none';

            // Redirigir al home después de un pequeño retraso
            setTimeout(() => {
              window.location.href = `https://${window.location.hostname}/`;
            }, 2000);
          } else {
            loginMessageErr.innerHTML = response.message;
            loginMessageErr.style.display = 'block';
            loginMessageOk.style.display = 'none';
          }
        }
      } catch (error) {
        console.error('Error en la solicitud de login:', error);
        loginMessageErr.innerHTML = 'Ha ocurrido un error, por favor intente nuevamente.';
        loginMessageErr.style.display = 'block';
        loginMessageOk.style.display = 'none';
      }
    });
  }
};
