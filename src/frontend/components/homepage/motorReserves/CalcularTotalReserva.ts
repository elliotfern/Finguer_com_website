import { validarFechas } from './ValidarFechas';
import { calcularTotalDiasReserva } from './CalcularTotalDiasReserva';
import { calcularPrecioSinIva } from './CalcularPrecioSinIva';
import { calcularPrecioConIva } from './CalcularPrecioConIva';

export const calcularTotalReserva = (): { precioTotal: number; costeSeguro: number; precioReserva: number; costeIva: number; precioSubtotal: number; costoLimpiezaSinIva: number; diasReserva: number } => {
  let precioTotal = 0;
  let precioSubtotal = 0;
  let costeIva = 0;
  let costeSeguro = 0;
  let diasReserva = 0;
  let costoReserva = 0;
  let precioReserva = 0;
  let costoLimpiezaSinIva = 0;
  const costoDia = 5;
  const porcentajeIva = 0.21;
  const costeReservaFinguerClass = 10.01;
  const costeReservaGoldClass = 30.01;

  const costeReservaFinguerClassSinIva = calcularPrecioSinIva(costeReservaFinguerClass, porcentajeIva).precioSinIva;
  const costeReservaGoldClassSinIva = calcularPrecioSinIva(costeReservaGoldClass, porcentajeIva).precioSinIva;

  const costoDiaSinIva = calcularPrecioSinIva(costoDia, porcentajeIva).precioSinIva;

  // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
  if (!validarFechas()) {
    return { precioTotal: 0, costeSeguro: 0, precioReserva: 0, costeIva: 0, precioSubtotal: 0, costoLimpiezaSinIva: 0, diasReserva: 0 };
  }

  // Cálculo del precio total y número de días
  const tipoReservaElement = document.getElementById('tipo_reserva') as HTMLSelectElement | null;

  if (tipoReservaElement) {
    const tipoReserva = tipoReservaElement.value;

    if (tipoReserva === 'finguer_class') {
      costoReserva += costeReservaFinguerClassSinIva;
    } else if (tipoReserva === 'gold_finguer') {
      costoReserva += costeReservaGoldClassSinIva;
    }
  }

  precioSubtotal = costoReserva;
  precioReserva = costoReserva;

  const fechaReserva = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  if (fechaReserva) {
    diasReserva = calcularTotalDiasReserva(fechaReserva);
  }

  precioReserva += diasReserva * costoDiaSinIva; // Añadir costo por día
  precioSubtotal += diasReserva * costoDiaSinIva; // Añadir costo por día

  const limpiezaElement = document.getElementById('limpieza') as HTMLInputElement | null;

  if (limpiezaElement) {
    const costoLimpieza = parseInt(limpiezaElement.value, 10) || 0; // Asegúrate de que sea un número

    if (costoLimpieza === 15) {
      costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
    } else if (costoLimpieza === 35) {
      costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
    } else if (costoLimpieza === 95) {
      costoLimpiezaSinIva = calcularPrecioSinIva(costoLimpieza, porcentajeIva).precioSinIva;
    }

    precioSubtotal += costoLimpiezaSinIva; // Añadir costo de limpieza al precio total
  }

  // Verificar si el cliente ha seleccionado el seguro de cancelación
  // Obtener el elemento de la radio button seleccionada por su nombre
  const seguroCancelacionElement = document.querySelector('input[name="seguroCancelacion"]:checked') as HTMLInputElement | null;

  // Obtener el valor del radio button seleccionado, o null si ninguno está seleccionado
  const seguroCancelacion = seguroCancelacionElement ? seguroCancelacionElement.value : null;

  // Si el cliente ha seleccionado 'Sí' en el seguro de cancelación
  if (seguroCancelacion === '1') {
    if (precioSubtotal <= 50) {
      costeSeguro = 15; // Si es menor a 50, el coste del seguro es 15 euros
    } else {
      costeSeguro = precioSubtotal * 0.1; // Calcular el 10% del precio total
    }

    precioSubtotal += costeSeguro; // Añadir el coste del seguro al precio total
  }

  // Calcular IVA (+21%)
  costeIva = calcularPrecioConIva(precioSubtotal, porcentajeIva).iva;

  // 5 - Calcula el Importe total iva incluido
  precioTotal = calcularPrecioConIva(precioSubtotal, porcentajeIva).precioConIva;

  // Actualización de los elementos DOM para mostrar el precio total y número de días
  const costeReservaElement = document.getElementById('costeReserva');
  const costeSeguroElement = document.getElementById('costeSeguro');
  const costeLimpiezaElement = document.getElementById('costeLimpieza');
  const totalElement = document.getElementById('total');
  const precioSubTotalElement = document.getElementById('subTotal');
  const ivaElement = document.getElementById('precio_iva');

  const reservaElement = document.getElementById('resumenReserva');
  //const diaEntradaElement = document.getElementById('diaEntrada');
  //const diaSalidaElement = document.getElementById('diaSalida');

  const horaEntradaElement = document.getElementById('horaEntrada') as HTMLInputElement | null;
  const horaSalidaElement = document.getElementById('horaSalida') as HTMLInputElement | null;

  // Obtener el valor de los campos, asegurándose de que no sean nulos
  const horaEntradaValue = horaEntradaElement ? horaEntradaElement.value : null;
  const horaSalidaValue = horaSalidaElement ? horaSalidaElement.value : null;

  const fechaReservaElement = document.getElementById('fecha_reserva') as HTMLInputElement | null;

  let fechaEntrada = '';
  let fechaSalida = '';

  // Verificar si el input existe y tiene un valor
  if (fechaReservaElement && fechaReservaElement.value) {
    const fechas = fechaReservaElement.value.split(' to '); // Asegúrate de que el delimitador es 'to' si es lo que usas

    // Convertir las fechas en objetos Date
    const fechaInicio: Date = new Date(fechas[0]);
    const fechaFin: Date = new Date(fechas[1]);

    // Guardar las fechas de entrada y salida como cadenas legibles, si lo necesitas
    fechaEntrada = fechaInicio.toLocaleDateString(); // Convertir a string en formato de fecha
    fechaSalida = fechaFin.toLocaleDateString(); // Convertir a string en formato de fecha
  }

  const diaEntradaElement = document.getElementById('diaEntrada');
  const diaSalidaElement = document.getElementById('diaSalida');

  console.log('calcular total reserva antes de leer elementos');
  if (reservaElement && diaEntradaElement && diaSalidaElement && totalElement && precioSubTotalElement && ivaElement && costeReservaElement && costeSeguroElement && costeLimpiezaElement && fechaEntrada && fechaSalida) {
    reservaElement.innerHTML = `Detalles de la Reserva:`;
    diaEntradaElement.innerHTML = `<strong>Día de entrada:</strong> ${fechaEntrada} // ${horaEntradaValue}h.`;
    diaSalidaElement.innerHTML = `<strong>Día de salida:</strong> ${fechaSalida} // ${horaSalidaValue}h.`;
    costeReservaElement.innerHTML = `<strong>Coste Reserva (${diasReserva} ${diasReserva > 1 ? 'días' : 'día'}):</strong> ${precioReserva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;

    if (costeSeguro !== 0) {
      costeSeguroElement.innerHTML = `Coste Seguro: ${costeSeguro.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
    } else {
      costeSeguroElement.innerHTML = ''; // Opcional: vacía el contenido si no quieres mostrar nada cuando es 0
    }

    if (costoLimpiezaSinIva !== 0) {
      costeLimpiezaElement.innerHTML = `Coste Limpieza: ${costoLimpiezaSinIva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
    } else {
      costeLimpiezaElement.innerHTML = ``;
    }

    precioSubTotalElement.innerHTML = `Subtotal: ${precioSubtotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € (sin IVA)`;
    ivaElement.innerHTML = `IVA (21%): ${costeIva.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} €`;
    totalElement.innerHTML = `Precio Total: ${precioTotal.toLocaleString('es-ES', { minimumFractionDigits: 2, maximumFractionDigits: 2 })} € IVA incluido`;

    // Mostrar los mensajes de precio y número de días
    precioSubTotalElement.style.display = 'block';
    totalElement.style.display = 'block';
    ivaElement.style.display = 'block';
    costeReservaElement.style.display = 'block';
    costeSeguroElement.style.display = 'block';
    costeLimpiezaElement.style.display = 'block';
    reservaElement.style.display = 'block';
    diaEntradaElement.style.display = 'block';
    diaSalidaElement.style.display = 'block';
  }

  return { precioTotal, costeSeguro, precioReserva, costeIva, precioSubtotal, costoLimpiezaSinIva, diasReserva };
};
