import { FormularioCompleto } from './FormularioCompleto';

const horaEntradaSelect = document.getElementById(
    'horaEntrada'
) as HTMLSelectElement | null;
const horaSalidaSelect = document.getElementById(
    'horaSalida'
) as HTMLSelectElement | null;
const botonPagar = document.getElementById('pagar') as HTMLButtonElement | null;
const tipoReservaSelect = document.getElementById(
    'tipo_reserva'
) as HTMLSelectElement | null;

let backendOk = false; // 👈 se activa cuando /cotizar responde ok

const verificarSelecciones = (): boolean => {
    const horaEntradaValue = horaEntradaSelect?.value ?? '';
    const horaSalidaValue = horaSalidaSelect?.value ?? '';
    return horaEntradaValue !== '' && horaSalidaValue !== '';
};

/**
 * Llama a esto desde scheduleCotizar:
 * - setBackendOk(true) cuando el backend responde ok
 * - setBackendOk(false) si error/invalid
 */
export const setBackendOk = (ok: boolean): void => {
    backendOk = ok;
    actualizarBotonPagar();
};

export const actualizarBotonPagar = (): void => {
    if (!botonPagar) return;

    const puedePagar =
        backendOk && verificarSelecciones() && FormularioCompleto();

    botonPagar.style.display = puedePagar ? 'block' : 'none';
};

// listeners (solo si existen)
horaEntradaSelect?.addEventListener('change', () => {
    backendOk = false; // 👈 cambia algo, obligamos a re-cotizar antes de pagar
    actualizarBotonPagar();
});
horaSalidaSelect?.addEventListener('change', () => {
    backendOk = false;
    actualizarBotonPagar();
});
tipoReservaSelect?.addEventListener('change', () => {
    backendOk = false;
    actualizarBotonPagar();
});

// Inicialmente oculto
if (botonPagar) {
    botonPagar.style.display = 'none';
}
