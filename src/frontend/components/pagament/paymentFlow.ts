import { recuperarCarroCompra } from './recuperarDadesCarritoCompra';
import { pagamentTargeta } from './pagamentTargeta';

export const executePaymentFlow = async (): Promise<void> => {
  console.log('PAYMENT FLOW START');

  const snapshot = await recuperarCarroCompra();

  if (!snapshot) {
    console.error('NO SNAPSHOT');
    return;
  }

  await pagamentTargeta();

  console.log('PAYMENT FLOW END');
};