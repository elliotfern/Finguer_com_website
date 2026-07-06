import { API_URL } from '../../config/environment';
import { fetchData } from '../../services/api/api';

interface ContactResponse {
    status: string;
    message: string;
}

interface ContactPayload {
    nombre: string;
    telefono: string;
    email: string;
    mensaje?: string;
    privacidad: boolean;
    form_start: number;
    website: string;
}

export const finguerAnualContactForm = () => {
    document.addEventListener('DOMContentLoaded', () => {
        initForm();
    });
};

const initForm = () => {
    const form = document.getElementById('form-anual-class') as HTMLFormElement;

    const okMsg = document.getElementById('formMessageOk') as HTMLElement;
    const errMsg = document.getElementById('formMessageErr') as HTMLElement;

    if (!form) return;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const nombre = (document.getElementById('nombre') as HTMLInputElement)
            .value;
        const telefono = (
            document.getElementById('telefono') as HTMLInputElement
        ).value;
        const email = (document.getElementById('email') as HTMLInputElement)
            .value;
        const mensaje = (
            document.getElementById('mensaje') as HTMLTextAreaElement
        ).value;

        const privacidad = (
            document.getElementById('privacidad') as HTMLInputElement
        ).checked;

        const submitBtn = form.querySelector(
            'button[type="submit"]'
        ) as HTMLButtonElement;
        let success = false;

        try {
            submitBtn.disabled = true;
            submitBtn.innerText = 'Enviando...';

            const response = await fetchData<ContactResponse, ContactPayload>(
                `${API_URL}/formulario/post`,
                'POST',
                {
                    nombre,
                    telefono,
                    email,
                    mensaje,
                    privacidad,
                    form_start: Date.now(),
                    website: '',
                }
            );

            if (!response) return;

            if (response.status === 'success') {
                success = true;
                if (okMsg) {
                    okMsg.innerHTML = response.message;
                    okMsg.classList.remove('d-none');
                }

                if (errMsg) {
                    errMsg.classList.add('d-none');
                    errMsg.innerHTML = '';
                }

                form.reset();
                form.querySelectorAll('input, textarea, button').forEach(
                    (el) => {
                        (
                            el as
                                | HTMLInputElement
                                | HTMLTextAreaElement
                                | HTMLButtonElement
                        ).disabled = true;
                    }
                );
                form.classList.add('opacity-75');
                submitBtn.innerText = 'Enviado ✔';
            } else {
                success = false;
                if (errMsg) {
                    errMsg.innerHTML = response.message || 'Error en el envío.';
                    errMsg.classList.remove('d-none');
                }

                if (okMsg) {
                    okMsg.classList.add('d-none');
                    okMsg.innerHTML = '';
                }
            }
        } catch (error) {
            if (errMsg) {
                errMsg.innerHTML = 'Ha ocurrido un error. Inténtalo de nuevo.';
                errMsg.classList.remove('d-none');
            }

            if (okMsg) {
                okMsg.classList.add('d-none');
                okMsg.innerHTML = '';
            }
        } finally {
            if (!success) {
                submitBtn.disabled = false;
                submitBtn.innerText = 'Solicitar información';
            }
        }
    });
};
