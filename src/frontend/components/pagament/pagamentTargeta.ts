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
  console.log('PAGAMENT TARGETA START');

  const session = getSessionFromUrl();

  console.log('SESSION', session);

  if (!session) {
    console.error('No session');
    return;
  }

  console.log('ANTES FETCH REDSYS');

  try {
    const response = await fetchData<ApiRespostaRedSys, PostRequest>(`${apiUrl}/pagamentRedsysTargeta`, 'POST', { session });
    console.log('REDSYS RESPONSE', response);

    if (!response) {
      console.error('REDSYS RESPONSE NULL');
      return;
    }

    if (response.status !== 'success') {
      console.error('REDSYS ERROR RESPONSE', response);
      return;
    }

    if (!response || response.status !== 'success' || !('params' in response) || !('signature' in response) || !('idReserva' in response)) {
      console.error('REDSYS INVALID RESPONSE', response);
      return;
    }
    
    const { params, signature, idReserva } = response;

    console.log('ANTES CREACIO USUARI');

    const r = await creacioDadesUsuaris(idReserva);
    console.log('DESPUES CREACIO USUARI', r);

    if (!r || r.status !== 'success') {
      console.error('Error creando datos usuario', r);
      return;
    }

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = redsysUrl;

    const addInput = (name: string, value: string) => {
      const input = document.createElement('input');
      input.type = 'hidden';
      input.name = name;
      input.value = value;
      form.appendChild(input);
    };

    addInput('Ds_SignatureVersion', 'HMAC_SHA256_V1');
    addInput('Ds_MerchantParameters', params);
    addInput('Ds_Signature', signature);

    document.body.appendChild(form);

    console.log('SUBMIT REDSYS');
    console.log('ANTES FORM SUBMIT');

    form.submit();
  } catch (err) {
    console.error('PAYMENT ERROR', err);
    return;
  }
};
