import { daterangepicker } from './components/DatePickRanger.js';
import { showPrice } from './components/ShowPrice.js';
import { sendForm } from './components/sendForm.js';
import { seleccionaHoraTipoReserva } from './components/seleccionaHoraTipoReserva.js'

document.addEventListener('DOMContentLoaded', function () {
  	daterangepicker();
	showPrice();
	sendForm(); 
	seleccionaHoraTipoReserva();
});