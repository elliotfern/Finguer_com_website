import { obrirFinestra, tancarFinestra } from './finestraEmergent/finestraEmergent';

export const reservesPendents = () => {

    import('./taulaReservesPendents/taulaReservesPendents').then(module => {
        module.carregarDadesTaulaReservesPendents();
    }).catch(error => {
        console.error('Error al cargar el módulo:', error);
    });

    import('./taulaReservesPendents/comptadorReservesPendents').then(module => {
        module.compatadorReservesPendents();
    }).catch(error => {
        console.error('Error al cargar el módulo:', error);
    });

    // Un único listener para manejar los dos botones
    document.addEventListener('click', (event) => {
        const target = event.target as HTMLElement;

        // Verificar si el elemento clickeado tiene la clase 'obrir-finestra-btn'
        if (target.classList.contains('obrir-finestra-btn')) {
            const id = target.getAttribute('data-id');
            if (id) {
                obrirFinestra(event, id);
            }
        }
        // Verificar si el elemento clickeado tiene la clase 'tancar-finestra-btn'
        else if (target.classList.contains('tancar-finestra-btn')) {
            tancarFinestra();
        }
    });
};