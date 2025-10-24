import { PaymentData } from '../../types/interfaces';
import { imprimirDadesReserva } from './imprimirDadesReserva';

export async function recuperarDadesLocalStorage(): Promise<PaymentData | null> {
  const sessionCode = window.location.pathname.split('/').pop() || null;
  if (!sessionCode) return null;

  try {
    const response = await fetch(`/api/carro-compra-session/?session=${sessionCode}`);
    if (!response.ok) throw new Error('No se pudo recuperar los datos del carrito');
    const data: PaymentData = await response.json();

    imprimirDadesReserva(data); // <- sigue pintando
    return data; // <- devuelve datos
  } catch (err) {
    console.error(err);
    return null;
  }
}
