// main.ts
import 'bootstrap/dist/css/bootstrap.min.css';
import './style.css';
import 'bootstrap';

// Obtener la ruta actual sin barra final
const path = window.location.pathname.replace(/\/$/, '');

// Web Finguer.com
// Importar mòduls per la homepage de Finguer.com
if (window.location.pathname === '/') {
  import('./components/homepage/homepage')
    .then((module) => {
      module.homePage();
    })
    .catch((error) => {
      console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Solo importar finestraEmergent.ts si estamos en la página correcta
if (path === '/pago') {
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
