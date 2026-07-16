import { ENDPOINTS } from '../../config/endpoints';
import { redsysUrl } from '../../config/environment';
import { fetchData } from '../../services/api/api';
import type { ApiRespostaRedSys } from '../../types/interfaces';
import { creacioDadesUsuaris } from './creacioDadesUsuari';

interface PostRequest {
    session: string;
}

const messageOk = document.getElementById('messageOk');
const messageErr = document.getElementById('messageErr');
const messageErrText = document.getElementById('messageErrText');

const mostrarOk = () => {
    messageOk?.classList.remove('d-none');
    messageErr?.classList.add('d-none');
};

const mostrarError = (texto?: string) => {
    messageErr?.classList.remove('d-none');
    messageOk?.classList.add('d-none');

    if (messageErrText) {
        messageErrText.textContent =
            texto ?? 'Se ha producido un error inesperado.';
    }
};

export const pagamentTargeta = async (): Promise<void> => {
    const session = window.location.pathname.split('/').filter(Boolean).pop();
    if (!session) return;

    const response = await fetchData<ApiRespostaRedSys, { session: string }>(
        `${ENDPOINTS.POST.pago.pagamentRedsysTargeta}`,
        'POST',
        { session }
    );

    if (!response || response.status !== 'success' || !response.data) {
        console.error('ERROR REDSYS');
        mostrarError(response?.message);
        return;
    }

    const { params, signature, idReserva } = response.data;

    const r = await creacioDadesUsuaris(idReserva);
    if (!r || r.status !== 'success') {
        console.error('ERROR CREANDO USUARIO');
        mostrarError(r?.message);
        return;
    }

    // 1. Validamos que la URL exista realmente
    if (!redsysUrl || redsysUrl.trim() === '') {
        console.error('Falta la URL de Redsys en la configuración');
        mostrarError();
        return;
    }

    // 2. Crear formulario
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = redsysUrl;

    // Buena práctica: ocultarlo para evitar flashes visuales raros en el DOM
    form.style.display = 'none';

    const mk = (n: string, v: string) => {
        const i = document.createElement('input');
        i.type = 'hidden';
        i.name = n;
        i.value = v;
        form.appendChild(i);
    };

    mk('Ds_SignatureVersion', 'HMAC_SHA256_V1');
    mk('Ds_MerchantParameters', params);
    mk('Ds_Signature', signature);

    document.body.appendChild(form);

    mostrarOk();

    // 3. Envío seguro
    requestAnimationFrame(() => {
        // Usamos requestAnimationFrame en lugar de setTimeout para asegurar
        // que el DOM ya ha pintado/registrado el formulario.
        try {
            form.submit();
        } catch (error) {
            console.error('Error al hacer submit a Redsys:', error);
        }
    });
};
