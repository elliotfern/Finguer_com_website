export const login = () => {
  document.addEventListener('DOMContentLoaded', () => {
    const btnLogin = document.getElementById('btnLogin') as HTMLButtonElement;
    const emailInput = document.getElementById('email') as HTMLInputElement;
    const passwordInput = document.getElementById('password') as HTMLInputElement;
    const loginMessageOk = document.getElementById('loginMessageOk') as HTMLElement;
    const loginMessageErr = document.getElementById('loginMessageErr') as HTMLElement;

    if (btnLogin) {
      btnLogin.addEventListener('click', async (event: MouseEvent) => {
        event.preventDefault();

        const email = emailInput.value;
        const password = passwordInput.value;

        try {
          const response = await fetch('https://' + window.location.hostname + '/api/intranet/auth/login/', {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({ email, password }),
          });

          const data = await response.json();

          if (data.status === 'success') {
            loginMessageOk.innerHTML = data.message;
            loginMessageOk.style.display = 'block';
            loginMessageErr.style.display = 'none';

            setTimeout(() => {
              window.location.href = 'https://' + window.location.hostname + '/control/reserves-pendents';
            }, 2000); // Redirige después de 3 segundos
          } else {
            loginMessageErr.innerHTML = data.message;
            loginMessageErr.style.display = 'block';
            loginMessageOk.style.display = 'none';
          }
        } catch (error) {
          console.error('Error en la solicitud:', error);
          loginMessageErr.innerHTML = 'Error al intentar iniciar sesión.';
          loginMessageErr.style.display = 'block';
          loginMessageOk.style.display = 'none';
        }
      });
    }
  });
};
