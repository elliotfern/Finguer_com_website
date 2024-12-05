import { Comptador } from '../../../../types/interfaces.js';

export const compatadorReservesPendents = async (): Promise<void> => {
    try {
        // Corregir la URL
        const url = `${window.location.origin}/api/intranet/reserves/get/?type=numReservesPendents`;
        
        const response = await fetch(url);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const datos: Comptador = await response.json();

        // Verifica si el elemento existe antes de modificarlo
        const numReservesPendentsElement = document.getElementById("numReservesPendents");
        if (numReservesPendentsElement) {
            // Si solo hay un dato de reservas pendientes
            if (datos) {
                numReservesPendentsElement.textContent = `Total reserves pendents d'entrar al parking: ${datos.numero}`;
            } else {
                numReservesPendentsElement.textContent = "No hi ha reserves pendents.";
            }
        }
    } catch (error) {
        console.error('Error al cargar los datos:', error);
    }
};