// recuperarDadesLocalStorage.ts
import { PaymentData } from '../../types/interfaces';

import { imprimirDadesReserva } from './imprimirDadesReserva';

export const recuperarDadesLocalStorage = () => {
  // Recuperar los datos almacenados
  const dataString = localStorage.getItem('paymentData');

  // Obtienes el elemento con el ID 'pantallaPagament'
  const pantallaPagament = document.getElementById('pantallaPagament') as HTMLDivElement | null;
  const pantallaPagamentError = document.getElementById('pantallaPagamentError') as HTMLDivElement | null;

  // Verificar si los datos existen
  if (dataString && pantallaPagament) {
    const data: PaymentData[] = JSON.parse(dataString);
    imprimirDadesReserva(data);

    // cal mostrar el div pantallaPagament
    pantallaPagament.style.display = 'block';
  } else {
    if (pantallaPagamentError) {
      pantallaPagamentError.style.display = 'block';
    }
  }
};
