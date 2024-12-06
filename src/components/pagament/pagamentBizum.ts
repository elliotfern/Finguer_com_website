// pagamentBizum.ts

import { creacioDadesUsuaris } from './creacioDadesUsuari';

export const pagamentBizum = async (): Promise<void> => {

  try {
    // Esperamos a que la función creacioDadesUsuaris termine
    const response = await creacioDadesUsuaris();

    // Si la respuesta es exitosa, continuamos con el resto de la función
    if (response.status === 'success') {

      // Si todo ha ido bien, entonces se envia al usuario a la pasarela de pago de Redsys, Bizum:

      // capturar els valors del formulari amb javascript
      const version = (document.getElementById('version') as HTMLInputElement)?.value || '';
      const params2 = (document.getElementById('params2') as HTMLInputElement)?.value || '';
      const signature2 = (document.getElementById('signature2') as HTMLInputElement)?.value || '';

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