<script>
$(document).ready(function() {
    $('#fecha_reserva').daterangepicker({
        autoApply: true,
        startDate: moment().add(2, 'days'),
        endDate: moment().add(2, 'days'),
        minDate: moment().startOf('day').add(2, 'days'), // No permitir seleccionar fechas anteriores a la fecha actual
        locale: {
            format: 'DD-MM-YYYY',
            firstDay: 1, // Configura el primer día de la semana como lunes (0 para domingo, 1 para lunes, 2 para martes, etc.)
            cancelLabel: 'Cancelar',
            applyLabel: 'Aplicar', // Etiqueta del botón Aplicar
            daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sá"], // Nombres de los días de la semana
            monthNames: [ // Nombres de los meses
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio",
                "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ]
        }
    });

    // Actualizar el estado del botón de pagar y calcular el precio total al cambiar el rango de fechas
    $('#fecha_reserva').on('change', function() {
        var fechas = $(this).val().split(" - ");
        if (fechas.length === 2) {
            // Calcular el costo total cuando se selecciona un rango de fechas
            calcularTotal();

            // Actualizar el estado del botón de pagar
            actualizarBotonPagar();
        }
    });

    // Función para verificar si las fechas seleccionadas son válidas
    function validarFechas() {
        var fechaReserva = $('#fecha_reserva').val();
        var fechas = fechaReserva.split(" - ");
        var fechaInicio = moment(fechas[0], 'DD-MM-YYYY');
        var fechaFin = moment(fechas[1], 'DD-MM-YYYY');
        var fechaActual = moment();
        
        // Calcular la diferencia en horas considerando tanto la fecha como la hora actual
        var horasDiferencia = fechaInicio.diff(fechaActual, 'hours', true);

        // Si la fecha de inicio es anterior a la fecha actual o dentro de las 12 horas
        if (fechaInicio.isBefore(fechaActual) || horasDiferencia < 12) {
            $('#mensaje_error').text('Tu fecha de llegada al parking debe ser al menos 12 horas después de la hora actual.');
            return false;
        }

        // Si la fecha de fin es anterior o igual a la fecha de inicio
        if (fechaFin.isSameOrBefore(fechaInicio)) {
            $('#mensaje_error').text('La fecha de fin debe ser posterior a la fecha de inicio.');
            return false;
        }

        $('#mensaje_error').text(''); // Limpiar mensaje de error si las fechas son válidas
        return true;
    }

    // Función para actualizar el estado del botón de pagar
    function actualizarBotonPagar() {
        if (validarFechas()) {
            $('#pagar').show(); // Mostrar el botón de pagar si las fechas son válidas
        } else {
            $('#pagar').hide(); // Ocultar el botón de pagar si hay errores en las fechas
        }
    }

// Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
function calcularTotal() {
    // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
    if (!validarFechas()) {
        return;
    }

    // Cálculo del precio total y número de días
    var tipoReserva = $('#tipo_reserva').val();
    var costoReserva = 0;

    // Determinar el costo según el tipo de reserva
    if (tipoReserva === 'finguer_class') {
        costoReserva += 10;
    } else if (tipoReserva === 'gold_finguer') {
        costoReserva += 25;
    }

    // Obtener fechas y calcular la diferencia de días
    var fechas = $('#fecha_reserva').val().split(" - ");
    var fechaInicio = moment(fechas[0], 'DD-MM-YYYY');
    var fechaFin = moment(fechas[1], 'DD-MM-YYYY');
    var diasReserva = fechaFin.diff(fechaInicio, 'days') + 1; // Sumar 1 para incluir el día de inicio
    costoReserva += diasReserva * 5;

    // Costo de limpieza
    var costoLimpieza = parseInt($('#limpieza').val());

    // Calcular el precio total sin seguro de cancelación
    var precioTotal = costoReserva + costoLimpieza;

    // Verificar si el cliente ha seleccionado el seguro de cancelación
    var seguroCancelacion = $('input[name="seguroCancelacion"]:checked').val();

    var costeSeguro = 0;

     // Si el cliente ha seleccionado 'Sí' en el seguro de cancelación
     if (seguroCancelacion === "1") {  // Comparar como string
        costeSeguro = precioTotal * 0.30; // Calcular el 30% del precio total

        if (costeSeguro < 12) {
            costeSeguro = 12; // Si el 30% es menor a 12, el coste del seguro es 12 euros
        }

        precioTotal += costeSeguro; // Añadir el coste del seguro al precio total
    }

    // Mostrar el precio total y los días de reserva
    $('#precio_total').text(precioTotal.toFixed(2));  // Redondear a 2 decimales
    $('#num_dias').text(diasReserva);
    $('#total, #dias').show(); // Mostrar los mensajes de precio y número de días

    // Retornar tanto el precio total como el coste del seguro para su uso posterior
    return { precioTotal, costeSeguro };
}

// Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
$('#tipo_reserva, #limpieza, input[name="seguroCancelacion"]').change(function() {
    calcularTotal();
});

    // Redirigir al usuario a la página de pago al hacer clic en el botón de pagar
    $('#pagar').click(function() {

        // Calcular el total y el coste del seguro de cancelación
        let { precioTotal, costeSeguro } = calcularTotal();

        // Crear un formulario dinámicamente
        let form = $('<form action="/pago/" method="post"></form>');

        // Obtener el valor del seguro de cancelación (Sí o No)
        let seguroCancelacionSeleccionado = $('input[name="seguroCancelacion"]:checked').val();
        let seguroTexto = seguroCancelacionSeleccionado === "1" ? "1" : "2";

        // Agregar las variables como campos ocultos al formulario
        form.append('<input type="hidden" name="precioReservaSinLimpieza" value="' + calcularTotalSinLimpieza() + '">');
        form.append('<input type="hidden" name="tipoReserva" value="' + $('#tipo_reserva').val() + '">');
        form.append('<input type="hidden" name="fechaEntrada" value="' + $('#fecha_reserva').val().split(' - ')[0] + '">');
        form.append('<input type="hidden" name="fechaSalida" value="' + $('#fecha_reserva').val().split(' - ')[1] + '">');
        form.append('<input type="hidden" name="limpieza" value="' + $('#limpieza').val() + '">');
        form.append('<input type="hidden" name="numDias" value="' + $('#num_dias').text() + '">');
        form.append('<input type="hidden" name="seguroCancelacion" value="' + seguroTexto + '">');

        // Agregar el coste del seguro de cancelación al formulario
        form.append('<input type="hidden" name="costeSeguro" value="' + costeSeguro.toFixed(2) + '">');

        // Adjuntar el formulario al cuerpo del documento y enviarlo
        $('body').append(form);
        form.submit();
    });

    // Función para calcular el precio total de la reserva sin incluir el costo de la limpieza
    function calcularTotalSinLimpieza() {
        // Verificar si las fechas seleccionadas son válidas antes de calcular el precio total
        if (!validarFechas()) {
            return;
        }

        // Cálculo del precio total sin incluir el costo de la limpieza
        var tipoReserva = $('#tipo_reserva').val();
        var costoReserva = 0;

        if (tipoReserva === 'finguer_class') {
            costoReserva += 10;
        } else if (tipoReserva === 'gold_finguer') {
            costoReserva += 25;
        }

        var fechas = $('#fecha_reserva').val().split(" - ");
        var fechaInicio = moment(fechas[0], 'DD-MM-YYYY');
        var fechaFin = moment(fechas[1], 'DD-MM-YYYY');
        var diasReserva = fechaFin.diff(fechaInicio, 'days') + 1; // Sumar 1 para incluir el día de inicio
        costoReserva += diasReserva * 5;

        return costoReserva; // Retorna el precio total sin limpieza
    }
});

</script>