// main.ts
import 'bootstrap/dist/css/bootstrap.min.css';
import './style.css';
import 'bootstrap';

// Obtener la ruta actual sin barra final
const path = window.location.pathname.replace(/\/$/, '');

// Web Finguer.com
// Importar mòduls per la homepage de Finguer.com
if (window.location.pathname === '/') {
    import('./components/homepage/homepage').then(module => {
        module.homePage();
    }).catch(error => {
        console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Solo importar finestraEmergent.ts si estamos en la página correcta
if (path === '/pago') {
    import('./components/pagament/pagament').then(module => {
        module.pagament();
    }).catch(error => {
        console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Intranet treballadors
// Solo importar finestraEmergent.ts si estamos en la página correcta
if (window.location.pathname === '/control/reserves-pendents') {
    import('./components/intranet/reserves/reservesPendents').then(module => {
        module.reservesPendents();
    }).catch(error => {
        console.error('Error al cargar el módulo de la homepage:', error);
    });
}