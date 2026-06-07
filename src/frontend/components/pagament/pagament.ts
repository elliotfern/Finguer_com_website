import { pagamentTargeta } from './pagamentTargeta';
import { botoPagament } from './botoPagament';
import { recuperarCarroCompra } from './recuperarDadesCarritoCompra';

let initialized = false;

export const pagament = async (): Promise<void> => {
  // Pégalo al principio de pagament.ts, antes de todo
window.addEventListener('beforeunload', () => {
  console.trace('🔴 PÁGINA SE VA A RECARGAR');
});

document.addEventListener('submit', (e) => {
  console.trace('🔴 SUBMIT CAPTURADO EN:', e.target);
  // NO pongas preventDefault aquí todavía, solo observa
});

  const snapshot = await recuperarCarroCompra();
  if (!snapshot) {
    console.error('No snapshot');
    return;
  }

  if (initialized) return;
  initialized = true;

  const checkbox = document.getElementById('terminos_condiciones') as HTMLInputElement | null;
  const button = document.getElementById('pagamentTargeta') as HTMLButtonElement | null;

  if (!checkbox || !button) return;

  // estado inicial
  botoPagament();

  // checkbox listener
  checkbox.addEventListener('change', () => {
    botoPagament();
  });

  // click DIRECTO (SIN DELEGACIÓN)
  button.addEventListener('click', async (e) => {
    e.preventDefault();
    e.stopPropagation();

    console.log('CLICK PAYMENT');

    const ok = checkbox.checked;
    if (!ok) {
      document.getElementById('aviso_terminos')!.style.display = 'block';
      return;
    }

    document.getElementById('aviso_terminos')!.style.display = 'none';

    const snapshot2 = await recuperarCarroCompra();
    if (!snapshot2) return;

    await pagamentTargeta();
  });
};