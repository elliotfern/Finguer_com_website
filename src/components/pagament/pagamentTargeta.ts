// pagamentTargeta.ts
import { creacioDadesUsuaris } from './creacioDadesUsuari';
import { fetchData } from '../../services/api/api';
import { PaymentData, ApiRespostaRedSys } from '../../types/interfaces';

interface PostRequest {
  costTotal: number | undefined;
}

export const pagamentTargeta = async (): Promise<void> => {
  // trucada a la API de redsys per generar objecte
  const dataString = localStorage.getItem('paymentData');
  let costTotal: number | undefined;
  if (dataString) {
    const data: PaymentData[] = JSON.parse(dataString);
    const dades = data[0];
    costTotal = dades.precioTotal;
  }

  const postData: PostRequest = { costTotal: costTotal };

  const response = await fetchData<ApiRespostaRedSys, PostRequest>(`https://${window.location.hostname}/api/pagamentRedsysTargeta`, 'POST', postData);

  let params = '';
  let signature = '';
  let idReserva = '';

  if (response) {
    if (response.status === 'success') {
      params = response.params;
      signature = response.signature;
      idReserva = response.idReserva;
    }
  }

  try {
    // Esperamos a que la función creacioDadesUsuaris termine
    const response = await creacioDadesUsuaris(idReserva);

    // Si la respuesta es exitosa, continuamos con el resto de la función
    if (response.status === 'success') {
      console.log('Los datos se han creado correctamente.');

      // Si todo ha ido bien, entonces se envia al usuario a la pasarela de pago de Redsys, targeta:

      // capturar els valors del formulari amb javascript
      const version = 'HMAC_SHA256_V1';
      const params2 = params || '';
      const signature2 = signature || '';

      // Crear el formulario de forma dinámica
      const form = document.createElement('form');
      form.action = 'https://sis.redsys.es/sis/realizarPago';
      form.method = 'POST';

      // Crear y agregar los campos ocultos al formulario
      const signatureVersionInput = document.createElement('input');
      signatureVersionInput.type = 'hidden';
      signatureVersionInput.name = 'Ds_SignatureVersion';
      signatureVersionInput.value = version;
      form.appendChild(signatureVersionInput);

      const merchantParametersInput = document.createElement('input');
      merchantParametersInput.type = 'hidden';
      merchantParametersInput.name = 'Ds_MerchantParameters';
      merchantParametersInput.value = params2;
      form.appendChild(merchantParametersInput);

      const signatureInput = document.createElement('input');
      signatureInput.type = 'hidden';
      signatureInput.name = 'Ds_Signature';
      signatureInput.value = signature2;
      form.appendChild(signatureInput);

      // Adjuntar el formulario al cuerpo del documento y enviarlo
      document.body.appendChild(form);
      form.submit();
    } else {
      // Procesar la respuesta
      const messageErr = document.querySelector('#messageErr') as HTMLElement;
      const messageOk = document.querySelector('#messageOk') as HTMLElement;

      if (messageErr && messageOk) {
        messageErr.style.display = 'block';
        messageOk.style.display = 'none';
      }
    }
  } catch (error) {
    // En caso de error en creacioDadesUsuaris
    console.error('Hubo un error al crear los datos:', error);
  }
};
