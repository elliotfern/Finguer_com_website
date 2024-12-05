// main.ts

// Importar mòduls per la homepage de Finguer.com
if (window.location.pathname === '/') {
    import('./components/homepage/homepage').then(module => {
        module.homePage();
    }).catch(error => {
        console.error('Error al cargar el módulo de la homepage:', error);
    });
}

// Solo importar finestraEmergent.ts si estamos en la página correcta
if (window.location.pathname === '/control/reserves-pendents') {
    import('./components/intranet/reserves/reservesPendents').then(module => {
        module.reservesPendents();
    }).catch(error => {
        console.error('Error al cargar el módulo de la homepage:', error);
    });
}