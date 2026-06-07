import { creacioDadesUsuaris } from './creacioDadesUsuari';
import { fetchData } from '../../services/api/api';
import type { ApiRespostaRedSys } from '../../types/interfaces';
import { apiUrl, redsysUrl } from '../../config/globals';

interface PostRequest {
  session: string;
}

function getSessionFromUrl(): string | null {
  const parts = window.location.pathname.split('/').filter(Boolean);
  return parts.length ? decodeURIComponent(parts[parts.length - 1]) : null;
}

export const pagamentTargeta = async (): Promise<void> => {
  console.log('=== PAGAMENT START ===');

  // 🧠 capturadores globales de errores (solo para debug staging)
  window.addEventListener('error', (e) => {
    console.error('🧨 JS ERROR:', e.message);
  });

  window.addEventListener('unhandledrejection', (e) => {
    console.error('🧨 PROMISE ERROR:', e.reason);
  });

  const session = getSessionFromUrl();
  console.log('SESSION:', session);

  if (!session) {
    console.error('NO SESSION');
    return;
  }

  try {
    console.log('STEP 1 - FETCH REDSYS START');

    const response = await fetchData<ApiRespostaRedSys, PostRequest>(
      `${apiUrl}/pagamentRedsysTargeta`,
      'POST',
      { session }
    );

    console.log('STEP 2 - FETCH RESPONSE:', response);

    if (!response) {
      console.error('RESPONSE NULL');
      return;
    }

    if (response.status !== 'success') {
      console.error('API ERROR RESPONSE:', response);
      return;
    }

    console.log('STEP 3 - VALID RESPONSE OK');

    const { params, signature, idReserva } = response;

    if (!params || !signature || !idReserva) {
      console.error('MISSING REDSYS FIELDS:', response);
      return;
    }

    console.log('STEP 4 - CREAR USUARIO');

    const r = await creacioDadesUsuaris(idReserva);

    console.log('STEP 5 - CREAR USUARIO RESPONSE:', r);

    if (!r || r.status !== 'success') {
      console.error('ERROR CREANDO USUARIO:', r);
      return;
    }

    console.log('STEP 6 - BUILD REDSYS FORM');

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = redsysUrl;

    const add = (name: string, value: string) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    };

    add('Ds_SignatureVersion', 'HMAC_SHA256_V1');
    add('Ds_MerchantParameters', params);
    add('Ds_Signature', signature);

    document.body.appendChild(form);

    console.log('STEP 7 - SUBMIT REDSYS');

    setTimeout(() => {
      form.submit();
    }, 0);

  } catch (err) {
    console.error('🔥 PAYMENT FATAL ERROR:', err);
  }
};