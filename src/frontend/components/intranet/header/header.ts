// header.ts
import { nomUsuari } from './nomUsuari';
import { logout } from './logout';

function initHeader(): void {
  nomUsuari();

  const logoutLink = document.querySelector('.link-sortir') as HTMLAnchorElement | null;
  if (logoutLink) {
    logoutLink.addEventListener('click', logout);
  }
}

export const header = () => {
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initHeader);
  } else {
    initHeader();
  }
};