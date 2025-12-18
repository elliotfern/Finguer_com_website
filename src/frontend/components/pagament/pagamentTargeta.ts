// pagamentTargeta.ts
import { creacioDadesUsuaris } from './creacioDadesUsuari';
import { fetchData } from '../../services/api/api';
import type { ApiRespostaRedSys } from '../../types/interfaces';
import { apiUrl, redsysUrl } from '../../config/globals';

interface PostRequest {
  session: string;
}

function getSessionFromUrl(): string | null {
  const parts = window.location.pathname.split('/').filter(Boolean);
  const last = parts[parts.length - 1];
  return last ? decodeURIComponent(last) : null;
}

export const pagamentTargeta = async (): Promise<void> => {
  const session = getSessionFromUrl();
  if (!session) {
    console.error('No se pudo determinar la sesión desde la URL.');
    return;
  }

  // El backend debe leer el total desde carro_compra usando session
  const postData: PostRequest = { session };

  const response = await fetchData<ApiRespostaRedSys, PostRequest>(`${apiUrl}/pagamentRedsysTargeta`, 'POST', postData);

  let params = '';
  let signature = '';
  let idReserva = '';

  if (response?.status === 'success') {
    params = response.params;
    signature = response.signature;
    idReserva = response.idReserva;
  } else {
    console.error('Error en respuesta de Redsys (preparación).', response);
    return;
  }

  try {
    // Crea cliente + reserva usando la session (carrito real en BD)
    const r = await creacioDadesUsuaris(idReserva);

    if (r.status === 'success') {
      const version = 'HMAC_SHA256_V1';

      const form = document.createElement('form');
      form.action = redsysUrl;
      form.method = 'POST';

      const signatureVersionInput = document.createElement('input');
      signatureVersionInput.type = 'hidden';
      signatureVersionInput.name = 'Ds_SignatureVersion';
      signatureVersionInput.value = version;
      form.appendChild(signatureVersionInput);

      const merchantParametersInput = document.createElement('input');
      merchantParametersInput.type = 'hidden';
      merchantParametersInput.name = 'Ds_MerchantParameters';
      merchantParametersInput.value = params;
      form.appendChild(merchantParametersInput);

      const signatureInput = document.createElement('input');
      signatureInput.type = 'hidden';
      signatureInput.name = 'Ds_Signature';
      signatureInput.value = signature;
      form.appendChild(signatureInput);

      document.body.appendChild(form);
      form.submit();
      return;
    }

    // Error al crear datos
    const messageErr = document.querySelector('#messageErr') as HTMLElement | null;
    const messageOk = document.querySelector('#messageOk') as HTMLElement | null;
    if (messageErr) messageErr.style.display = 'block';
    if (messageOk) messageOk.style.display = 'none';
  } catch (error) {
    console.error('Hubo un error al crear los datos:', error);
  }
};
