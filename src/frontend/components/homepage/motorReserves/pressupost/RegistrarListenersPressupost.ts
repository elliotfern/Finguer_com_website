// ShowPrice.ts
import { schedulePressupost } from './schedulePressupost';

export const RegistrarListenersPressupost = () => {
    const tipoReservaElement = document.getElementById(
        'tipo_reserva'
    ) as HTMLSelectElement | null;
    const limpiezaElement = document.getElementById(
        'limpieza'
    ) as HTMLInputElement | null;

    const seguroSiElement = document.getElementById(
        'seguroSi'
    ) as HTMLInputElement | null;
    const seguroNoElement = document.getElementById(
        'seguroNo'
    ) as HTMLInputElement | null;

    const horaEntradaElement = document.getElementById(
        'horaEntrada'
    ) as HTMLInputElement | null;
    const horaSalidaElement = document.getElementById(
        'horaSalida'
    ) as HTMLInputElement | null;

    if (
        tipoReservaElement &&
        limpiezaElement &&
        seguroSiElement &&
        seguroNoElement &&
        horaEntradaElement &&
        horaSalidaElement
    ) {
        tipoReservaElement.addEventListener('change', () =>
            schedulePressupost()
        );
        limpiezaElement.addEventListener('change', () => schedulePressupost());
        seguroSiElement.addEventListener('change', () => schedulePressupost());
        seguroNoElement.addEventListener('change', () => schedulePressupost());
        horaEntradaElement.addEventListener('change', () =>
            schedulePressupost()
        );
        horaSalidaElement.addEventListener('change', () =>
            schedulePressupost()
        );
    }
};
