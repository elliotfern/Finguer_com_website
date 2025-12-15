import { enviarConfirmacioReserva } from './enviarConfirmacioReserva';

type DeviceInfo = {
  dispositiu?: string;
  navegador?: string;
  sistema_operatiu?: string;
  ip?: string;
};
type DeviceInfoInput = DeviceInfo | DeviceInfo[] | null | undefined;

const clamp = (min: number, val: number, max: number): number => Math.max(min, Math.min(val, max));

/** Inserta (o actualiza) el bloque de información del dispositivo justo antes del botón "Tancar finestra". */
function renderDeviceInfoBlock(container: HTMLElement, data: DeviceInfoInput): void {
  const info: DeviceInfo = Array.isArray(data) ? data[0] ?? {} : data ?? {};

  const existing = container.querySelector('#deviceInfoBlock');
  if (existing) existing.remove();

  const block = document.createElement('div');
  block.id = 'deviceInfoBlock';
  block.style.marginBottom = '0.75rem';
  block.style.padding = '0.75rem';
  block.style.border = '1px solid rgba(0,0,0,.1)';
  block.style.borderRadius = '8px';
  block.style.fontSize = '0.95rem';

  block.innerHTML = `
    <div><strong>Dispositiu:</strong> ${info.dispositiu ?? '-'}</div>
    <div><strong>Navegador:</strong> ${info.navegador ?? '-'}</div>
    <div><strong>Sistema Operatiu:</strong> ${info.sistema_operatiu ?? '-'}</div>
    <div><strong>IP:</strong> ${info.ip ?? '-'}</div>
  `;

  const byId = container.querySelector('#btnTancarFinestra');
  const byData = container.querySelector('[data-role="close-popup"]');
  const byText = Array.from(container.querySelectorAll('button, a')).find((el) => (el.textContent ?? '').trim().toLowerCase().includes('tancar finestra'));

  const closeBtn = byId ?? byData ?? byText;

  if (closeBtn && closeBtn.parentElement) {
    closeBtn.parentElement.insertBefore(block, closeBtn);
  } else {
    container.prepend(block);
  }
}

/** Devuelve un DOMRect a partir de un evento o elemento; si no se puede, centra en el viewport. */
function getSourceRect(opener: MouseEvent | HTMLElement | null): DOMRect {
  // 1) Si es evento: prioriza currentTarget, luego target
  if (opener && typeof (opener as MouseEvent).type === 'string') {
    const ev = opener as MouseEvent;
    const cur = ev.currentTarget;
    if (cur instanceof Element) {
      return cur.getBoundingClientRect();
    }
    const tgt = ev.target;
    if (tgt instanceof Element) {
      return tgt.getBoundingClientRect();
    }
  }

  // 2) Si es un elemento directamente
  if (opener instanceof Element) {
    return opener.getBoundingClientRect();
  }

  // 3) Fallback: rect de 1x1 centrado
  const w = 1;
  const h = 1;
  const cx = Math.max(0, window.innerWidth / 2 - w / 2);
  const cy = Math.max(0, window.innerHeight / 2 - h / 2);
  return new DOMRect(cx, cy, w, h);
}

/** Posiciona el popup (fixed) cerca del rectángulo origen y lo encaja dentro del viewport. */
function positionPopupFixed(popup: HTMLElement, sourceRect: DOMRect): void {
  // Preparar para medir
  popup.style.position = 'fixed';
  popup.style.maxWidth = 'min(480px, 90vw)';
  popup.style.maxHeight = '90vh';
  popup.style.overflow = 'auto';
  popup.style.zIndex = '9999';

  const prevDisplay = popup.style.display;

  popup.style.display = 'block';
  popup.style.visibility = 'hidden';

  const width = popup.offsetWidth;
  const height = popup.offsetHeight;
  const margin = 8;

  // Horizontal: centrado respecto al "opener", encajonado
  let left = sourceRect.left + sourceRect.width / 2 - width / 2;
  left = clamp(margin, left, window.innerWidth - width - margin);

  // Vertical: intenta abajo; si no cabe, arriba; encajona
  let top = sourceRect.bottom + margin;
  if (top + height > window.innerHeight - margin) {
    top = sourceRect.top - height - margin;
  }
  top = clamp(margin, top, window.innerHeight - height - margin);

  popup.style.left = `${left}px`;
  popup.style.top = `${top}px`;

  popup.style.visibility = 'visible';
  if (prevDisplay === 'none') {
    // Lo dejamos visible
  }
  // prevVisibility lo dejamos en 'visible'
}

/**
 * Abre "ventanaEmergente" dentro del viewport, actualiza enlaces,
 * prepara el botón de confirmación y añade el bloque de Device Info antes de "Tancar finestra".
 *
 * Llamadas válidas:
 *   obrirFinestra(event, id, deviceInfo)
 *   obrirFinestra(document.getElementById('miBoton') as HTMLElement, id, deviceInfo)
 *   obrirFinestra(null, id, deviceInfo)  // se centra en pantalla
 */
export const obrirFinestra = (opener: MouseEvent | HTMLElement | null, id: string, deviceInfo?: DeviceInfoInput): void => {
  const urlWeb = window.location.origin + '/control';
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement | null;

  // Botón Confirmación
  const btnConfirmacio = document.getElementById('enlace1') as HTMLButtonElement | null;

  if (btnConfirmacio) {
    btnConfirmacio.textContent = 'Enviar confirmació email';
    btnConfirmacio.disabled = false;
    btnConfirmacio.style.cursor = 'pointer';
    btnConfirmacio.style.opacity = '1';
    btnConfirmacio.classList.remove('btn-success', 'btn-danger');
    btnConfirmacio.classList.add('btn-secondary');

    // Para evitar listeners duplicados
    const nuevoBtnConfirmacio = btnConfirmacio.cloneNode(true) as HTMLButtonElement;
    btnConfirmacio.parentNode?.replaceChild(nuevoBtnConfirmacio, btnConfirmacio);

    nuevoBtnConfirmacio.addEventListener('click', async (ev: MouseEvent) => {
      ev.preventDefault();

      // UI loading
      //const originalText = nuevoBtnConfirmacio.textContent || 'Enviar confirmació email';
      nuevoBtnConfirmacio.disabled = true;
      nuevoBtnConfirmacio.textContent = 'Enviant...';
      nuevoBtnConfirmacio.classList.remove('btn-success', 'btn-danger', 'btn-secondary');
      nuevoBtnConfirmacio.classList.add('btn-warning');

      try {
        const r = await enviarConfirmacioReserva(id);

        // OK
        nuevoBtnConfirmacio.disabled = false; // ✅ permitir reenviar
        nuevoBtnConfirmacio.textContent = 'Email enviat! (Reenviar)';
        nuevoBtnConfirmacio.classList.remove('btn-warning', 'btn-danger', 'btn-secondary');
        nuevoBtnConfirmacio.classList.add('btn-success');

        // Si quieres: mostrar r.message en un toast/alert en tu UI
        console.log(r.message);
      } catch (err) {
        const msg = err instanceof Error ? err.message : 'Error enviant email';

        // Error
        nuevoBtnConfirmacio.disabled = false; // ✅ permitir reintentar
        nuevoBtnConfirmacio.textContent = 'Error enviant (Reintentar)';
        nuevoBtnConfirmacio.classList.remove('btn-warning', 'btn-success', 'btn-secondary');
        nuevoBtnConfirmacio.classList.add('btn-danger');

        console.error(msg);
        // opcional: alert(msg);
      } finally {
        // opcional: si prefieres volver al texto inicial tras X segundos:
        // setTimeout(() => { nuevoBtnConfirmacio.textContent = originalText; }, 4000);
      }
    });
  }

  // Enlaces
  (document.getElementById('enlace2') as HTMLAnchorElement | null)?.setAttribute('href', `${urlWeb}/reserva/email/factura/${id}`);
  (document.getElementById('enlace3') as HTMLAnchorElement | null)?.setAttribute('href', `${urlWeb}/reserva/modificar/reserva/${id}`);
  (document.getElementById('enlace4') as HTMLAnchorElement | null)?.setAttribute('href', `${urlWeb}/reserva/eliminar/reserva/${id}`);

  if (!ventana) return;

  // Bloque de Device Info
  renderDeviceInfoBlock(ventana, deviceInfo);

  // Posicionamiento robusto (fixed + clamp)
  const srcRect = getSourceRect(opener);
  positionPopupFixed(ventana, srcRect);

  // Mostrar
  ventana.style.display = 'block';
};

/** Cierra la ventana emergente. */
export const tancarFinestra = (): void => {
  const ventana = document.getElementById('ventanaEmergente') as HTMLElement | null;
  if (ventana) ventana.style.display = 'none';
};
