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

  if (!session) {
    console.error('NO SESSION');
    return;
  }

  const response = await fetchData<ApiRespostaRedSys, PostRequest>(
    `${apiUrl}/pagamentRedsysTargeta`,
    'POST',
    { session }
  );

  if (!response || response.status !== 'success') {
    console.error('REDSYS ERROR', response);
    return;
  }

  const { params, signature, idReserva } = response;

  const r = await creacioDadesUsuaris(idReserva);

  if (!r || r.status !== 'success') {
    console.error('ERROR USUARIO');
    return;
  }

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = redsysUrl;

  const add = (n: string, v: string) => {
    const i = document.createElement('input');
    i.type = 'hidden';
    i.name = n;
    i.value = v;
    form.appendChild(i);
  };

  add('Ds_SignatureVersion', 'HMAC_SHA256_V1');
  add('Ds_MerchantParameters', params);
  add('Ds_Signature', signature);

  document.body.appendChild(form);

  console.log('SUBMIT REDSYS');

  form.submit();
};