// main.ts
import { detectAndRedirect } from './utils/selectorIdioma';
import 'bootstrap/dist/css/bootstrap.min.css';
import './style.css';
import 'bootstrap';

detectAndRedirect();

// Obtener la ruta actual sin barra final
//const path = window.location.pathname.replace(/\/$/, '');

const supportedLanguages = ['es', 'fr', 'en', 'ca']; // Idiomas soportados
// Normalizamos la ruta, eliminando la barra final si está presente
const normalizedPath = window.location.pathname.replace(/\/$/, '');

// Verificamos si la ruta es "/reserva" o "/{idioma}/reserva"
const isReservaPage = normalizedPath === '/reserva' || supportedLanguages.some((lang) => normalizedPath.startsWith(`/${lang}/reserva`));

const isPagoPage = normalizedPath === '/pago' || supportedLanguages.some((lang) => normalizedPath.startsWith(`/${lang}/pago`));

// Web Finguer.com
// Importar mòduls per la homepage de Finguer.com
if (isReservaPage) {
  import('./components/homepage/homepage')
    .then((module) => {
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
if (window.location.pathname === '/control/reserves-pendents') {
  import('./components/intranet/reserves/reservesPendents')
    .then((module) => {
      module.reservesPendents();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
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
