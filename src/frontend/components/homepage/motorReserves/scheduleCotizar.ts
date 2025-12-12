// src/pages/home/motorReserves/scheduleCotizar.ts
import { cotizarCarroBackend } from './carroCompraBackend';
import { renderCotizacion, type CotizarResponse } from './renderPrecio';
import { setBackendOk } from './ActualizarBotonPagar';

let t: number | null = null;

export function scheduleCotizar(ms = 350): void {
  if (t) window.clearTimeout(t);

  t = window.setTimeout(async () => {
    const msg = document.getElementById('mensaje_error');
    if (msg) msg.textContent = '';

    try {
      const data: CotizarResponse = await cotizarCarroBackend();

      if (!data.ok) {
        setBackendOk(false);
        return;
      }

      renderCotizacion(data);
      console.log('COTIZACIÓN OK, backendOk = true');
      setBackendOk(true);
    } catch (e) {
      if (msg) msg.textContent = 'Error de red o servidor';
      setBackendOk(false);
      console.log(e);
    }
  }, ms);
}
