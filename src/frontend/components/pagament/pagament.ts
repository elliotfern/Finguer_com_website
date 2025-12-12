import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';
import { recuperarCarroCompra } from './recuperarDadesCarritoCompra';

export const pagament = async (): Promise<void> => {
  const snapshot = await recuperarCarroCompra(); // <- debe devolver CarroSnapshot | null
  if (!snapshot) {
    console.error('No se pudo obtener los datos del carrito.');
    return;
  }

  botoPagament();

  const targetaButton = document.getElementById('pagamentTargeta') as HTMLButtonElement | null;
  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;
  const aviso = document.getElementById('aviso_terminos') as HTMLElement | null;

  const handlePayment = (callback: () => void) => {
    if (!checkbox?.checked) {
      if (aviso) aviso.style.display = 'block';
      return;
    }
    if (aviso) aviso.style.display = 'none';
    callback();
  };

  if (targetaButton) {
    targetaButton.addEventListener('click', () => handlePayment(() => pagamentTargeta()));
  }

  if (checkbox) {
    checkbox.addEventListener('change', botoPagament);
  }
};
