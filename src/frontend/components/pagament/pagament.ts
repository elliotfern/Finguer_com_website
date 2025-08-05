import { pagamentBizum } from './pagamentBizum';
import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';
import { recuperarDadesLocalStorage } from './recuperarDadesLocalStorage';
import { PaymentData } from '../../types/interfaces';

export async function pagament() {
  // Recupera datos de la API y los pinta en el DOM
  const dades = await recuperarDadesLocalStorage();
  if (!dades) {
    console.error('No se pudo obtener los datos del carrito.');
    return;
  }

  // Inicializa estado del botón de pago
  botoPagament();

  // Referencias a elementos
  const bizumButton = document.getElementById('pagamentBizum');
  const targetaButton = document.getElementById('pagamentTargeta');
  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement;
  const aviso = document.getElementById('aviso_terminos');

  const handlePayment = (callback: (d: PaymentData) => void) => {
    if (!checkbox?.checked) {
      if (aviso) aviso.style.display = 'block';
      return;
    }
    if (aviso) aviso.style.display = 'none';
    callback(dades); // Pasar datos reales a la función de pago
  };

  if (bizumButton) {
    bizumButton.addEventListener('click', () => handlePayment(pagamentBizum));
  }

  if (targetaButton) {
    targetaButton.addEventListener('click', () => handlePayment(pagamentTargeta));
  }

  if (checkbox) {
    checkbox.addEventListener('change', botoPagament);
  }
}
