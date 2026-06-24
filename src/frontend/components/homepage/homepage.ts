// homepage.ts
import { DateRangePicker } from './motorReserves/calendari/DateRangePicker';
import { seleccionaHoraTipoReserva } from './motorReserves/calendari/seleccionaHoraTipoReserva';
import { RedirigirAPagament } from './motorReserves/pagament/RedirigirAPagament';
import { RegistrarListenersPressupost } from './motorReserves/pressupost/RegistrarListenersPressupost';

export const homePage = () => {
    DateRangePicker();
    RegistrarListenersPressupost();
    seleccionaHoraTipoReserva();
};

// Seleccionar todos los botones con la clase "payButton"
const payButtons = document.querySelectorAll<HTMLButtonElement>('.payButton');

// Iterar sobre los botones y añadirles el evento "click"
payButtons.forEach((button) => {
    button.addEventListener('click', (event: MouseEvent) => {
        event.preventDefault(); // Evitar comportamiento predeterminado
        RedirigirAPagament();
    });
});
