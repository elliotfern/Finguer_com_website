// homepage.ts
import { daterangepicker } from './motorReserves/DatePickRanger';
import { showPrice } from './motorReserves/ShowPrice';
import { sendForm } from './motorReserves/sendForm';
import { seleccionaHoraTipoReserva } from './motorReserves/seleccionaHoraTipoReserva';

export const homePage = () => {
    daterangepicker();
    showPrice();
    sendForm();
    seleccionaHoraTipoReserva();
};