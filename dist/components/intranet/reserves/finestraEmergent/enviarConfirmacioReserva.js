// enviarConformacioReserva.ts
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
export const enviarConfirmacioReserva = (id) => __awaiter(void 0, void 0, void 0, function* () {
    const url = `${window.location.origin}/api/intranet/email/get/?type=emailConfirmacioReserva&id=${id}`;
    const response = yield fetch(url);
    if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
    }
    const text = yield response.text(); // Leer la respuesta como texto
    console.log('Respuesta cruda:', text); // Verifica el contenido
    let datos;
    try {
        datos = JSON.parse(text); // Intentar convertir a JSON
    }
    catch (e) {
        console.error('Error al analizar JSON:', e);
        throw new Error('Error al analizar JSON');
    }
    if (datos.message === "success") {
        const boton = document.getElementById('enlace1');
        if (boton) {
            boton.textContent = "Email enviat!";
            // Cambiar el estilo del botón (puedes agregar una clase CSS como ejemplo)
            boton.classList.add("btn-success"); // Cambiar el color del botón
            boton.classList.remove("btn-secondary"); // Eliminar el estilo original
            // Desactivar el cursor para reflejar el estado desactivado visualmente
            boton.style.cursor = "not-allowed";
            boton.style.opacity = "0.5";
        }
    }
    else {
        // Aquí podrías manejar otros casos si la respuesta no es "success"
        console.log("Error al enviar el email");
    }
});
