// main.ts
import { detectAndRedirect } from './utils/selectorIdioma';
import 'bootstrap/dist/css/bootstrap.min.css';
import './style.css';
import 'bootstrap';

import { reserves } from './components/intranet/reserves/reservesPendents';
import { initTaulaFacturacio } from './components/intranet/facturacio/llistatFactures';
import { clientsUsersTable } from './components/intranet/clients/llistatClients';
import { formUsuarios } from './components/intranet/clients/formClient';
import { reservesClientPage } from './components/intranet/clients/reservesClient';

import { nomUsuari } from './components/intranet/header/nomUsuari';
import { setMe } from './components/intranet/auth/store';
import { applyRoleToDom } from './components/intranet/auth/applyRole';

const supportedLanguages = ['es', 'fr', 'en', 'ca'] as const;

// Ruta normalizada: sin barra final (excepto "/")
const path = window.location.pathname.replace(/\/$/, '') || '/';

// Normalizamos para la web multidioma (usa mismo path normalizado)
const isReservaPage =
  path === '/reserva' || supportedLanguages.some((lang) => path.startsWith(`/${lang}/reserva`));

const isPagoPage =
  path === '/pago' ||
  supportedLanguages.some((lang) => path.startsWith(`/${lang}/pago`)) ||
  /^\/pago\/[a-zA-Z0-9]+$/.test(path);

function getUuidFromPath(prefix: string): string | undefined {
  // prefix ejemplo: '/control/usuaris/modifica-client'
  const p = window.location.pathname.replace(/\/+$/, ''); // quita trailing /
  if (!p.startsWith(prefix + '/')) return undefined;

  const uuid = p.slice(prefix.length + 1); // lo que viene después del /
  return uuid ? decodeURIComponent(uuid) : undefined;
}

// --------------------
// Web Finguer.com
// --------------------
if (isReservaPage) {
  import('./components/homepage/homepage')
    .then((module) => {
      detectAndRedirect();
      module.homePage();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

if (isPagoPage) {
  import('./components/pagament/pagament')
    .then((module) => {
      detectAndRedirect();
      module.pagament();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de pagament:', error);
    });
}

// --------------------
// Área cliente
// --------------------
if (path === '/area-cliente/login') {
  import('./components/areaClient/areaClientLogin')
    .then((module) => {
      detectAndRedirect();
      module.areaClientLogin();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo areaClientLogin:', error);
    });
}

if (path === '/area-cliente/reservas') {
  import('./components/areaClient/areaClientHistoricReserves')
    .then((module) => {
      detectAndRedirect();
      module.areaClientHistoricReserves();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo areaClientHistoricReserves:', error);
    });
}

// --------------------
// Intranet
// --------------------
const isIntranet = path === '/control' || path.startsWith('/control/');

async function initIntranet(): Promise<void> {
  // 1) cargar usuario + guardar en store
  const me = await nomUsuari();
  setMe(me);

  // 2) renderizar header (para que existan nodos como #userDiv)
  const headerModule = await import('./components/intranet/header/header');
  headerModule.header();

  // 3) aplicar permisos visuales sobre HTML existente (PHP)
  if (me) applyRoleToDom(me.role);

  // 4) cargar la página concreta de intranet
  routeIntranet(path);
}

function routeIntranet(p: string): void {
  // RESERVES
  if (p === '/control' || p === '/control/reserves-pendents') {
    reserves('pendiente_entrada');
    return;
  }
  if (p === '/control/reserves-parking') {
    reserves('dentro');
    return;
  }
  if (p === '/control/reserves-completades') {
    reserves('salido');
    return;
  }

  // CLIENTS ANUALS (tipo=3)
  if (p === '/control/clients-anuals/pendents') {
    reserves('pendiente_entrada', '3');
    return;
  }
  if (p === '/control/clients-anuals/parking') {
    reserves('dentro', '3');
    return;
  }
  if (p === '/control/clients-anuals/completades') {
    reserves('salido', '3');
    return;
  }

  // FACTURACIÓ
  if (p === '/control/facturacio') {
    initTaulaFacturacio();
    return;
  }

  // USUARIS
  if (p === '/control/usuaris') {
    clientsUsersTable();
    return;
  }

  if (p === '/control/usuaris/alta-client') {
    formUsuarios(false);
    return;
  }

  if (p.startsWith('/control/usuaris/modifica-client/')) {
    const uuid = getUuidFromPath('/control/usuaris/modifica-client');
    formUsuarios(true, uuid);
    return;
  }

  if (p.startsWith('/control/usuaris/reserves-client')) {
    reservesClientPage();
    return;
  }
}

// Arranque intranet (excepto login)
if (isIntranet && path !== '/control/login') {
  initIntranet().catch((error) => {
    console.error('Error intranet init:', error);
  });
}

// LOGIN intranet (separado a propósito)
if (path === '/control/login') {
  import('./components/intranet/login/login')
    .then((module) => {
      module.login();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo login:', error);
    });
}
