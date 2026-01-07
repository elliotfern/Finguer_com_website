// main.ts
import { detectAndRedirect } from './utils/selectorIdioma';
import 'bootstrap/dist/css/bootstrap.min.css';
import './style.css';
import 'bootstrap';
import { reserves } from './components/intranet/reserves/reservesPendents';
import { initTaulaFacturacio } from './components/intranet/facturacio/llistatFactures';
import { clientsUsersTable } from './components/intranet/clients/llistatClients';

// Obtener la ruta actual sin barra final
//const path = window.location.pathname.replace(/\/$/, '');

const supportedLanguages = ['es', 'fr', 'en', 'ca']; // Idiomas soportados
// Normalizamos la ruta, eliminando la barra final si está presente
const normalizedPath = window.location.pathname.replace(/\/$/, '');

// Verificamos si la ruta es "/reserva" o "/{idioma}/reserva"
const isReservaPage = normalizedPath === '/reserva' || supportedLanguages.some((lang) => normalizedPath.startsWith(`/${lang}/reserva`));

const isPagoPage = normalizedPath === '/pago' || supportedLanguages.some((lang) => normalizedPath.startsWith(`/${lang}/pago`)) || /^\/pago\/[a-zA-Z0-9]+$/.test(normalizedPath);

// Web Finguer.com
// Importar mòduls per la homepage de Finguer.com
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

// Solo importar finestraEmergent.ts si estamos en la página correcta
if (isPagoPage) {
  import('./components/pagament/pagament')
    .then((module) => {
      detectAndRedirect();
      module.pagament();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Area client
// pàgina login
if (window.location.pathname === '/area-cliente/login') {
  import('./components/areaClient/areaClientLogin')
    .then((module) => {
      detectAndRedirect();
      module.areaClientLogin();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// pagina principal area client
if (window.location.pathname === '/area-cliente/reservas') {
  import('./components/areaClient/areaClientHistoricReserves')
    .then((module) => {
      detectAndRedirect();
      module.areaClientHistoricReserves();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Intranet treballadors

// TOTA LA INTRANET
if (window.location.pathname.startsWith('/control/') && !window.location.pathname.includes('/control/login')) {
  // Esto se ejecutará en cualquier página que contenga "/control/"
  import('./components/intranet/header/header')
    .then((module) => {
      module.header();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de reservesPendents:', error);
    });
}

// RESERVES PENDENTS
if (window.location.pathname === '/control/reserves-pendents' || window.location.pathname === '/control') {
  const estatParking = 'pendiente_entrada';
  reserves(estatParking);
}

// RESERVES PARKING
if (window.location.pathname === '/control/reserves-parking') {
  const estatParking = 'dentro';
  reserves(estatParking);
}

// RESERVES COMPLETADES - mostrar nomes les ultimes 20
if (window.location.pathname === '/control/reserves-completades') {
  const estatParking = 'salido';
  reserves(estatParking);
}

// CLIENTS ANUALS - RESERVES PENDENTS PARKING
if (window.location.pathname === '/control/clients-anuals/pendents') {
  const estatParking = 'pendiente_entrada';
  reserves(estatParking, '3');
}

// CLIENTS ANUALS - RESERVES DINS AL PARKING
// RESERVES PARKING
if (window.location.pathname === '/control/clients-anuals/parking') {
  const estatParking = 'dentro';
  reserves(estatParking, '3');
}

// CLIENTS ANUALS - RESERVES PENDENTS PARKING
if (window.location.pathname === '/control/clients-anuals/completades') {
  const estatParking = 'salido';
  reserves(estatParking, '3');
}

// FACTURACIO
if (window.location.pathname === '/control/facturacio') {
  initTaulaFacturacio();
}

// LOGIN
if (window.location.pathname === '/control/login') {
  import('./components/intranet/login/login')
    .then((module) => {
      module.login();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// CONTROL CLIENTS
if (window.location.pathname === '/control/clients') {
  clientsUsersTable();
}