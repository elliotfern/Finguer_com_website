// types/interfaces.ts (ejemplo)
export interface ComptadorReserves {
  pendiente_entrada: number;
  dentro: number;
  salido: number;
}

export const comptadorReserves = (estatParking: string, counts: ComptadorReserves | undefined): void => {
  const numReservesElement = document.getElementById('numReserves');
  if (!numReservesElement || !counts) return;

  let text = '';

  switch (estatParking) {
    case 'pendiente_entrada':
      text = `Total reserves pendents d'entrar al parking: ${counts.pendiente_entrada}`;
      break;
    case 'dentro':
      text = `Total vehicles actualment dins del parking: ${counts.dentro}`;
      break;
    case 'salido':
      text = `Total reserves completades (vehicles sortits): ${counts.salido}`;
      break;
    default:
      text = '';
  }

  numReservesElement.textContent = text;
};
