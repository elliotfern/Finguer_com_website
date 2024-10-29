import { calcularTotalReserva } from "./CalcularTotalReserva.js";
export const sendForm = () => {
    // Redirigir al usuario a la página de pago al hacer clic en el botón de pagar
    // Obtener el elemento por su ID
    const pagarButton = document.getElementById('pagar');
    // Verificar si el botón existe
    if (pagarButton) {
        pagarButton.addEventListener('click', function (event) {
            var _a, _b, _c;
            event.preventDefault(); //
            // Llamar a calcularTotal y desestructurar el resultado
            const { costeSeguro, precioSinLimpieza } = calcularTotalReserva();
            // Crear el formulario
            const form = document.createElement('form');
            form.action = '/pago/';
            form.method = 'post';
            // Función para crear y agregar inputs ocultos al formulario
            const addHiddenInput = (name, value) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = name;
                input.value = value;
                form.appendChild(input);
            };
            // Crear un objeto con los datos del formulario
            const formData = {
                precioReservaSinLimpieza: String(precioSinLimpieza),
                tipoReserva: ((_a = document.getElementById('tipo_reserva')) === null || _a === void 0 ? void 0 : _a.value) || '',
                fechaEntrada: '',
                fechaSalida: '',
                limpieza: ((_b = document.getElementById('limpieza')) === null || _b === void 0 ? void 0 : _b.value) || '',
                numDias: ((_c = document.getElementById('num_dias')) === null || _c === void 0 ? void 0 : _c.innerText) || '',
            };
            // Obtener las fechas del elemento de reserva
            const fechaReservaElement = document.getElementById('fecha_reserva');
            if (fechaReservaElement) {
                const fechas = fechaReservaElement.value.split(' - ');
                formData.fechaEntrada = fechas[0] || '';
                formData.fechaSalida = fechas[1] || '';
            }
            // Obtener el valor del seguro de cancelación (Sí o No)
            const seguroCancelacionSeleccionado = $('input[name="seguroCancelacion"]:checked').val();
            const seguroTexto = seguroCancelacionSeleccionado === '1' ? '1' : '2';
            // Agregar el seguro de cancelación al objeto formData
            formData.seguroCancelacion = seguroTexto;
            // Agregar el coste del seguro de cancelación al objeto formData
            addHiddenInput('costeSeguro', costeSeguro.toFixed(2)); // Asegúrate de que se agrega correctamente
            // Agregar los campos ocultos al formulario
            for (const [name, value] of Object.entries(formData)) {
                addHiddenInput(name, value);
            }
            // Adjuntar el formulario al cuerpo del documento y enviarlo
            document.body.appendChild(form);
            form.submit();
        });
    }
};
