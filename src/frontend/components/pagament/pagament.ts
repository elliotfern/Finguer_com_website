import { botoPagament } from './botoPagament';
import { executePaymentFlow } from './paymentFlow';
import { recuperarCarroCompra } from './recuperarDadesCarritoCompra';

let paymentInitialized = false;

export const pagament = async (): Promise<void> => {
  if (paymentInitialized) return;
  paymentInitialized = true;

  const snapshot = await recuperarCarroCompra();
  if (!snapshot) {
    console.error('NO SNAPSHOT');
    return;
  }

  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;

  if (checkbox) {
    checkbox.addEventListener('change', botoPagament);
    botoPagament();
  }

  document.addEventListener('click', onPaymentClick);
};

const onPaymentClick = async (e: Event) => {
  const target = e.target as HTMLElement;
  const button = target.closest('#pagamentTargeta');

  if (!button) return;

  e.preventDefault();
  e.stopPropagation();

  console.log('CLICK REAL');

  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;

  if (!checkbox?.checked) {
    console.log('CHECKBOX NOT OK');
    return;
  }

  console.log('GO PAYMENT FLOW');

  await executePaymentFlow();
};