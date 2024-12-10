// header.ts

import { nomUsuari } from './nomUsuari';
import { logout } from './logout';

export const header = () => {
  // Asegúrate de que el DOM esté listo antes de ejecutar las funciones
  document.addEventListener('DOMContentLoaded', () => {
    nomUsuari();

    // Selecciona el enlace de logout y agrega el manejador de eventos
    const logoutLink = document.querySelector('.link-sortir') as HTMLElement;
    if (logoutLink) {
      logoutLink.addEventListener('click', logout);
    }
  });
};
