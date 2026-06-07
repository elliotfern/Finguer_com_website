import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';
import { recuperarCarroCompra } from './recuperarDadesCarritoCompra';

let paymentInitialized = false;

export const pagament = async (): Promise<void> => {

  window.addEventListener('beforeunload', () => {
  console.log('PAGE IS RELOADING OR NAVIGATING');
});

setInterval(() => {
  console.log('ALIVE CHECK');
}, 1000);


  const snapshot = await recuperarCarroCompra();

  if (!snapshot) {
    console.error('No se pudo obtener los datos del carrito.');
    return;
  }

  // Evita re-inicializar listeners (CRÍTICO)
  if (paymentInitialized) return;
  paymentInitialized = true;

  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;
  if (!checkbox) {
    return;
  }

  checkbox.addEventListener('change', botoPagament);
  botoPagament(); // inicializa estado

  document.addEventListener('click', async (e) => {
    const target = e.target as HTMLElement;

    const button = target.closest('#pagamentTargeta');
    if (!button) return;

    //

    console.log('CLICK REAL');

    e.preventDefault();
    e.stopPropagation();

    console.log('PREVENT DEFAULT OK');

    //

    e.preventDefault();

    console.log('CLICK PAGO');

    const snapshot = await recuperarCarroCompra();

    console.log('SNAPSHOT', snapshot);

    if (!snapshot) {
      console.log('SNAPSHOT NULL');
      return;
    }

    console.log('AFTER SNAPSHOT');

    const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;

    console.log('CHECKBOX', checkbox?.checked);

    if (!checkbox?.checked) {
      console.log('CHECKBOX NOT OK');
      return;
    }

    console.log('GO REDSYS');

    await pagamentTargeta();

    console.log('REDSYS RETURNED');
  });
};
