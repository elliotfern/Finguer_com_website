// recuperarDadesLocalStorage.ts
import { PaymentData } from '../../types/interfaces';
import { imprimirDadesReserva } from './imprimirDadesReserva';

export const recuperarDadesLocalStorage = async (): Promise<PaymentData | null> => {
  console.log('hem arribat fins aqui?');

  // Obtener el código de sesión desde la URL
  function getSessionCode(): string | null {
    const path = window.location.pathname;
    const segments = path.split('/');
    return segments[segments.length - 1] || null; // La última parte de la URL
  }

  const sessionCode = getSessionCode();

  // Función para obtener los datos del carrito
  async function obtenerCarrito(sessionCode: string): Promise<PaymentData | null> {
    try {
      const response = await fetch(`/api/carro-compra-session/?session=${sessionCode}`);
      if (!response.ok) {
        throw new Error('No se pudo recuperar los datos del carrito');
      }
      const data: PaymentData = await response.json();
      console.log('Datos del carrito:', data);
      return data;
    } catch (error) {
      console.error('Error al obtener los datos del carrito:', error);
      return null;
    }
  }

  // Realizar la consulta si el código de sesión está presente
  if (sessionCode) {
    try {
      const data = await obtenerCarrito(sessionCode);
      if (data) {
        // Llamar a la función imprimirDadesReserva con los datos obtenidos
        imprimirDadesReserva(data);
        //console.log(data);
        return data; // Devolver los datos del carrito
      } else {
        console.error('No se encontraron datos del carrito');
        return null;
      }
    } catch (error) {
      console.error('Error al obtener los datos del carrito:', error);
      return null;
    }
  } else {
    console.error('Código de sesión no encontrado en la URL');
    return null;
  }
};
