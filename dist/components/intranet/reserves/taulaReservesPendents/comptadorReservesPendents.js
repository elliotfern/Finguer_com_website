var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
export const compatadorReservesPendents = () => __awaiter(void 0, void 0, void 0, function* () {
    try {
        // Corregir la URL
        const url = `${window.location.origin}/api/intranet/reserves/get/?type=numReservesPendents`;
        const response = yield fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const datos = yield response.json();
        // Verifica si el elemento existe antes de modificarlo
        const numReservesPendentsElement = document.getElementById("numReservesPendents");
        if (numReservesPendentsElement) {
            // Si solo hay un dato de reservas pendientes
            if (datos) {
                numReservesPendentsElement.textContent = `Total reserves pendents d'entrar al parking: ${datos.numero}`;
            }
            else {
                numReservesPendentsElement.textContent = "No hi ha reserves pendents.";
            }
        }
    }
    catch (error) {
        console.error('Error al cargar los datos:', error);
    }
});
