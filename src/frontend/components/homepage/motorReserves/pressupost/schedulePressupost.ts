// src/pages/home/motorReserves/scheduleCotizar.ts
import { setBackendOk } from '../formulari/ActualizarBotonPagar';
import { pressupostCarroBackend } from './carroCompraBackend';
import { mostrarPreu, type CotizarResponse } from './mostrarPreu';

let t: number | null = null;

export function schedulePressupost(ms = 350): void {
    if (t) window.clearTimeout(t);

    t = window.setTimeout(async () => {
        const msg = document.getElementById('mensaje_error');
        if (msg) msg.textContent = '';

        try {
            const data: CotizarResponse = await pressupostCarroBackend();

            if (!data.ok) {
                setBackendOk(false);
                return;
            }

            mostrarPreu(data);
            setBackendOk(true);
        } catch (e) {
            if (msg) msg.textContent = 'Error de red o servidor';
            setBackendOk(false);
        }
    }, ms);
}
