// homepage.ts
import { daterangepicker } from './motorReserves/DatePickRanger';
import { showPrice } from './motorReserves/ShowPrice';
import { handleClickPagament } from './motorReserves/localStorage';
import { seleccionaHoraTipoReserva } from './motorReserves/seleccionaHoraTipoReserva';

export const homePage = () => {
  daterangepicker();
  showPrice();
  seleccionaHoraTipoReserva();
};

// Seleccionar todos los botones con la clase "payButton"
const payButtons = document.querySelectorAll<HTMLButtonElement>('.payButton');

// Iterar sobre los botones y añadirles el evento "click"
payButtons.forEach((button) => {
  button.addEventListener('click', (event: MouseEvent) => {
    event.preventDefault(); // Evitar comportamiento predeterminado
    handleClickPagament();
  });
});
