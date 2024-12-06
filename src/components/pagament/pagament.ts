import { pagamentBizum } from './pagamentBizum';
import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';

export const pagament = () => {
  document.addEventListener('DOMContentLoaded', () => {
    botoPagament();

    // Seleccionar los botones directamente
    const bizumButton = document.getElementById('pagamentBizum');
    const targetaButton = document.getElementById('pagamentTargeta');
    const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement;
    const aviso = document.getElementById('aviso_terminos');

    const handlePayment = (callback: () => void) => {
      if (!checkbox?.checked) {
        if (aviso) aviso.style.display = 'block';
        return;
      }
    
      if (aviso) aviso.style.display = 'none';
      callback();
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
  });
};

