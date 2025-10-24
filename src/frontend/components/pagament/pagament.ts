//import { pagamentBizum } from './pagamentBizum';
import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';
import { recuperarDadesLocalStorage } from './recuperarDadesLocalStorage';
import { PaymentData } from '../../types/interfaces';

export const pagament = async () => {
  const dades = await recuperarDadesLocalStorage(); // Esperar a obtener los datos
  if (!dades) {
    console.error('No se pudo obtener los datos del carrito.');
    return;
  }

  botoPagament();

  // Seleccionar los botones directamente
  //const bizumButton = document.getElementById('pagamentBizum');
  const targetaButton = document.getElementById('pagamentTargeta');
  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement;
  const aviso = document.getElementById('aviso_terminos');

  const handlePayment = (callback: (dades: PaymentData) => void) => {
    if (!checkbox?.checked) {
      if (aviso) aviso.style.display = 'block';
      return;
    }

    if (aviso) aviso.style.display = 'none';
    callback(dades); // Pasar dades a la función de pago
  };

  /*
    if (bizumButton) {
      bizumButton.addEventListener('click', () => handlePayment(pagamentBizum));
    }
      */

  if (targetaButton) {
    targetaButton.addEventListener('click', () => handlePayment(pagamentTargeta));
  }

  if (checkbox) {
    checkbox.addEventListener('change', botoPagament);
  }
};
