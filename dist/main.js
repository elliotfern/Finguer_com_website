"use strict";
// main.ts
// Importar mòduls per la homepage de Finguer.com
if (window.location.pathname === '/') {
    import('./components/homepage/homepage.js').then(module => {
        const { homePage } = module;
        homePage();
    });
}
// Solo importar finestraEmergent.ts si estamos en la página correcta
if (window.location.pathname === '/control/reserves-pendents') {
    import('./components/intranet/reserves/finestraEmergent.js').then(module => {
        const { obrirFinestra, tancarFinestra } = module;
        // Verifica si los elementos existen antes de añadir los event listeners
        const abrirBtn = document.getElementById('obrirFinestraBtn');
        const cerrarBtn = document.getElementById('cerrarVentanaBtn');
        if (abrirBtn) {
            abrirBtn.addEventListener('click', (event) => {
                // Aquí obtenemos el ID directamente del botón utilizando el atributo data-id
                const id = abrirBtn.getAttribute('data-id');
                if (id) {
                    obrirFinestra(event, id);
                }
            });
        }
        if (cerrarBtn) {
            cerrarBtn.addEventListener('click', () => {
                // Puedes pasar otros parámetros si es necesario
                tancarFinestra();
            });
        }
    });
}
