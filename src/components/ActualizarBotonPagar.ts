import { validarFechas } from "./ValidarFechas.js";

export const actualizarBotonPagar = () => {
    if (validarFechas()) {
      $('#pagar').show(); // Mostrar el botón de pagar si las fechas son válidas
    } else {
      $('#pagar').hide(); // Ocultar el botón de pagar si hay errores en las fechas
    }
  }
  