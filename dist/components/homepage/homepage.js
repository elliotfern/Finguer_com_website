// homepage.ts
import { daterangepicker } from './motorReserves/DatePickRanger.js';
import { showPrice } from './motorReserves/ShowPrice.js';
import { sendForm } from './motorReserves/sendForm.js';
import { seleccionaHoraTipoReserva } from './motorReserves/seleccionaHoraTipoReserva.js';
export const homePage = () => {
    document.addEventListener('DOMContentLoaded', function () {
        daterangepicker();
        showPrice();
        sendForm();
        seleccionaHoraTipoReserva();
    });
};
