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
  console.log('PAYMENT START');

  const session = window.location.pathname.split('/').filter(Boolean).pop();
  if (!session) return;

  const response = await fetchData<ApiRespostaRedSys, { session: string }>(
    `${apiUrl}/pagamentRedsysTargeta`,
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

  const form = document.createElement('form');
  form.method = 'POST';
  form.action = redsysUrl;

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

  console.log('SUBMIT REDSYS');

  // 🔥 IMPORTANTE: evita cualquier interferencia
  setTimeout(() => {
    form.submit();
  }, 50);
};