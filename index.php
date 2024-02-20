<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finguer - Reservas</title>
    <!-- Agrega los scripts de Stripe y jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/i18n/jquery-ui-i18n.min.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
</head>

<body>
    
    <style>
        /* Estilos adicionales pueden ir aquí */
        .highlight {
            background-color: #FFFF66 !important; /* Cambia el color de fondo a amarillo */
        }
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f9fa;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border-radius: 10px;
            box-shadow: 0px 0px 10px 0px rgba(0,0,0,0.1);
        }
        h1 {
            text-align: center;
            margin-bottom: 30px;
        }
        label {
            font-weight: bold;
        }
        select, input[type="text"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
        }
        #mensaje_error {
            margin-top: 10px;
            font-size: 14px;
        }
        #total, #dias {
            font-size: 16px;
            font-weight: bold;
            margin-top: 20px;
        }
        #pagar {
            width: 100%;
            padding: 10px;
            background-color: #007bff;
            color: #fff;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        #pagar:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container">
    <h1>Selecciona tus fechas de reserva</h1>

    <!-- Selección del tipo de reserva -->
    <label for="tipo_reserva">Tipo de Reserva:</label>
    <select id="tipo_reserva" name="tipo_reserva">
        <option value="finguer_class">FINGUER CLASS</option>
        <option value="gold_finguer">GOLD FINGUER</option>
    </select>

    <!-- Calendario para elegir fecha de entrada y salida -->
    <form id="reservation-form">
        <label for="fecha_reserva">Fechas de Reserva:</label>
        <input type="text" id="fecha_reserva" name="fecha_reserva" readonly>

        <!-- Opciones de limpieza -->
        <label for="limpieza">Selecciona tu limpieza:</label>
        <select id="limpieza" name="limpieza">
            <option value="0">Sin limpieza</option>
            <option value="15">Servicio de limpieza exterior - 15 euros</option>
            <option value="25">Servicio de lavado exterior + aspirado tapicería interior - 25 euros</option>
            <option value="55">Lavado PRO. Lo dejamos como nuevo - 55 euros</option>
        </select>

        <!-- Espacio para mostrar el precio total y el número de días -->
        <p id="total" style="display: none;">Precio Total: <span id="precio_total"></span> IVA incluido</p>
        <p id="dias" style="display: none;">Número de días: <span id="num_dias"></span></p>

        <!-- Espacio para mostrar mensajes de error -->
        <div id="mensaje_error" style="color: red;"></div>

        <!-- Botón de pagar -->
        <button type="button" id="pagar" style="display: none;">Pagar</button>
    </form>
    </div>

    <script>
        $(document).ready(function() {
            // Configurar localización en español y primer día de la semana como lunes
            $.datepicker.setDefaults($.datepicker.regional['es']);
            $.datepicker.setDefaults({
                firstDay: 1
            });

            // Inicializar datepicker para el campo de fecha de reserva con opción de rango
            $("#fecha_reserva").datepicker({
                dateFormat: "dd-mm-yy",
                minDate: 0, // No permitir seleccionar fechas anteriores a la fecha actual
                numberOfMonths: 2, // Mostrar dos meses
                onSelect: function(selectedDate) {
                    if (!$("#fecha_reserva").data("start")) {
                        $("#fecha_reserva").data("start", selectedDate);
                    } else {
                        var startDate = $("#fecha_reserva").data("start");
                        var endDate = selectedDate;
                        var range = startDate + " - " + endDate;
                        $("#fecha_reserva").val(range);
                        $("#fecha_reserva").removeData("start");
                    }

                    // Calcular el costo total cuando se selecciona una fecha
                    calcularTotal();

                    // Actualizar el estado del botón de pagar
                    actualizarBotonPagar();
                },
                beforeShowDay: function(date) {
                    var highlight = $.datepicker.formatDate("yy-mm-dd", date);
                    var start = $.datepicker.formatDate("yy-mm-dd", new Date($("#fecha_reserva").data("start")));
                    var end = $("#fecha_reserva").val().split(" - ")[1];
                    return [true, (highlight >= start && highlight <= end) ? "highlight" : ""];
                }
            });

            // Función para verificar si las fechas seleccionadas son válidas
            function validarFechas() {
                var fechaReserva = $('#fecha_reserva').val();
                var fechas = fechaReserva.split(" - ");
                var fechaInicio = $.datepicker.parseDate("dd-mm-yy", fechas[0]);
                var fechaFin = $.datepicker.parseDate("dd-mm-yy", fechas[1]);
                var fechaActual = new Date();
                var diferencia = fechaInicio - fechaActual;
                var horasDiferencia = Math.floor(diferencia / (1000 * 60 * 60));

                // Si la fecha de inicio es anterior a la fecha actual o dentro de las 12 horas
                if (fechaInicio < fechaActual || horasDiferencia < 12) {
                    $('#mensaje_error').text('La fecha de inicio debe ser posterior a la fecha actual y al menos 12 horas en el futuro.');
                    return false;
                }

                // Si la fecha de fin es anterior o igual a la fecha de inicio
                if (fechaFin <= fechaInicio) {
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

                if (tipoReserva === 'finguer_class') {
                    costoReserva += 10;
                } else if (tipoReserva === 'gold_finguer') {
                    costoReserva += 25;
                }

                var fechas = $('#fecha_reserva').val().split(" - ");
                var fechaInicio = $.datepicker.parseDate("dd-mm-yy", fechas[0]);
                var fechaFin = $.datepicker.parseDate("dd-mm-yy", fechas[1]);
                var diasReserva = Math.floor((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1; // Sumar 1 para incluir el día de inicio
                costoReserva += diasReserva * 5;
    
                var costoLimpieza = parseInt($('#limpieza').val());

                var precioTotal = costoReserva + costoLimpieza;
                $('#precio_total').text(precioTotal);
                $('#num_dias').text(diasReserva);
                $('#total, #dias').show(); // Mostrar los mensajes de precio y número de días
                return precioTotal;
            }

            // Calcular y mostrar el precio total y el número de días al cambiar cualquier elemento de selección o campo de fecha
            $('#tipo_reserva, #limpieza').change(function() {
                calcularTotal();
            });

            // Redirigir al usuario a la página de pago al hacer clic en el botón de pagar
            $('#pagar').click(function() {
                var precioReservaSinLimpieza = calcularTotalSinLimpieza(); // Nuevo
                var tipoReserva = $('#tipo_reserva').val();
                var fechaEntrada = $('#fecha_reserva').val().split(' - ')[0];
                var fechaSalida = $('#fecha_reserva').val().split(' - ')[1];
                var limpieza = $('#limpieza').val();
                var numDias = $('#num_dias').text();

                // Construir la URL con los detalles de la reserva
                var url = 'pagina_pago.php?precio_reserva_sin_limpieza=' + precioReservaSinLimpieza + '&tipo_reserva=' + tipoReserva + '&fecha_entrada=' + fechaEntrada + '&fecha_salida=' + fechaSalida + '&limpieza=' + limpieza + '&diasReserva=' + numDias;
                
                // Redirigir al usuario
                window.location.href = url;
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
            var fechaInicio = $.datepicker.parseDate("dd-mm-yy", fechas[0]);
            var fechaFin = $.datepicker.parseDate("dd-mm-yy", fechas[1]);
            var diasReserva = Math.floor((fechaFin - fechaInicio) / (1000 * 60 * 60 * 24)) + 1; // Sumar 1 para incluir el día de inicio
            costoReserva += diasReserva * 5;

            return costoReserva; // Retorna el precio total sin limpieza
        }


        });
    </script>
</body>
</html>
