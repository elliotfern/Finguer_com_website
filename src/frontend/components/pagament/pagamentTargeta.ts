import { API_URL, redsysUrl } from '../../config/environment';
import { fetchData } from '../../services/api/api';
import type { ApiRespostaRedSys } from '../../types/interfaces';
import { creacioDadesUsuaris } from './creacioDadesUsuari';

interface PostRequest {
    session: string;
}

function getSessionFromUrl(): string | null {
    const parts = window.location.pathname.split('/').filter(Boolean);
    return parts.length ? decodeURIComponent(parts[parts.length - 1]) : null;
}

export const pagamentTargeta = async (): Promise<void> => {
    console.log('PAYMENT START');

    const session = window.location.pathname.split('/').filter(Boolean).pop();
    if (!session) return;

    const response = await fetchData<ApiRespostaRedSys, { session: string }>(
        `${API_URL}/pagamentRedsysTargeta`,
        'POST',
        { session }
    );

    console.log('REDSYS RESPONSE', response);

    if (!response || response.status !== 'success') {
        console.error('ERROR REDSYS');
        return;
    }

    const { params, signature, idReserva } = response;

    const r = await creacioDadesUsuaris(idReserva);
    if (!r || r.status !== 'success') {
        console.error('ERROR CREANDO USUARIO');
        return;
    }

    // 1. Validamos que la URL exista realmente
    if (!redsysUrl || redsysUrl.trim() === '') {
        console.error('Falta la URL de Redsys en la configuración');
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

    console.log('ENVIANDO FORMULARIO A REDSYS...');

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
