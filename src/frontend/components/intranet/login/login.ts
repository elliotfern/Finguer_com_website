import { ENDPOINTS } from '../../../config/endpoints';

export const login = () => {
    document.addEventListener('DOMContentLoaded', () => {
        const btnLogin = document.getElementById(
            'btnLogin'
        ) as HTMLButtonElement;
        const emailInput = document.getElementById('email') as HTMLInputElement;
        const passwordInput = document.getElementById(
            'password'
        ) as HTMLInputElement;
        const loginMessageOk = document.getElementById(
            'okMessage'
        ) as HTMLElement;
        const loginMessageErr = document.getElementById(
            'errMessage'
        ) as HTMLElement;

        if (btnLogin) {
            btnLogin.addEventListener('click', async (event: MouseEvent) => {
                event.preventDefault();

                const email = emailInput.value;
                const password = passwordInput.value;

                try {
                    const response = await fetch(ENDPOINTS.POST.auth.login, {
                        method: 'POST',
                        credentials: 'include',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({ email, password }),
                    });

                    const data = await response.json();

                    if (data.status === 'success') {
                        loginMessageOk.innerHTML = data.message;

                        loginMessageOk.classList.remove('d-none');
                        loginMessageErr.classList.add('d-none');

                        setTimeout(() => {
                            window.location.href = `${window.location.origin}/control/reserves-pendents`;
                        }, 2000);
                    } else {
                        loginMessageErr.innerHTML = data.message;

                        loginMessageErr.classList.remove('d-none');
                        loginMessageOk.classList.add('d-none');
                    }
                } catch (error) {
                    loginMessageErr.innerHTML =
                        'Error al intentar iniciar sesión.';

                    loginMessageErr.classList.remove('d-none');
                    loginMessageOk.classList.add('d-none');
                }
            });
        }
    });
};
