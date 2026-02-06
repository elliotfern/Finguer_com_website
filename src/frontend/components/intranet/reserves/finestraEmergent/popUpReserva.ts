import { apiUrl } from '../../../../config/globals';
import { enviarConfirmacioReserva } from './enviarConfirmacioReserva';
import { isAdmin } from '../../auth/store';

type DeviceInfo = {
  dispositiu?: string;
  navegador?: string;
  sistema_operatiu?: string;
  ip?: string;
};
type DeviceInfoInput = DeviceInfo | DeviceInfo[] | null | undefined;

const POPUP_ID = 'ventanaEmergente';

const clamp = (min: number, val: number, max: number): number => Math.max(min, Math.min(val, max));

function applyVisibilityRules(container: HTMLElement, estadoReserva?: string | null): void {
  const admin = isAdmin();
  const pagada = estadoReserva === 'pagada';

  const btnConfirm = container.querySelector('#enlace1') as HTMLElement | null;
  const btnFactura = container.querySelector('#enlace2') as HTMLElement | null;
  const btnCancelar = container.querySelector('#enlace4') as HTMLElement | null;

  // Solo admin
  if (btnConfirm) btnConfirm.style.display = admin ? '' : 'none';
  if (btnFactura) btnFactura.style.display = admin ? '' : 'none';

  // Cancelar: solo admin + NO pagada
  if (btnCancelar) btnCancelar.style.display = admin && !pagada ? '' : 'none';
}

function isHTMLElement(x: unknown): x is HTMLElement {
  return x instanceof HTMLElement;
}

function ensurePopupContainer(): HTMLElement | null {
  const el = document.getElementById(POPUP_ID);
  return el instanceof HTMLElement ? el : null;
}

/** Genera el HTML del popup dentro del contenedor (si aún no existe). */
function ensurePopupMarkup(container: HTMLElement): void {
  // Si ya está “montado”, no lo recrees
  if (container.querySelector('[data-popup-inner="1"]')) return;

  container.innerHTML = `
    <div class="contenidoVentana" data-popup-inner="1">
      <div class="container">
        <div class="row">
          <div class="col-12 col-md-12 d-flex flex-column justify-content-between gap-3">
            <button id="enlace1" class="btn btn-secondary w-100 w-md-auto btn-sm" type="button">
              Enviar confirmació
            </button>

            <button id="enlace2" class="btn btn-secondary w-100 w-md-auto btn-sm" type="button">
              Enviar factura
            </button>

            <a href="#" id="enlace3" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button">
              Modificar reserva
            </a>

            <a href="#" id="enlace4" class="btn btn-secondary w-100 w-md-auto btn-sm" role="button" data-requires-role="admin">
              Cancel·lar reserva
            </a>

            <button id="btnTancarFinestra" class="btn btn-danger tancar-finestra-btn w-100 w-md-auto btn-sm" type="button" data-role="close-popup">
              Tancar
            </button>
          </div>
        </div>
      </div>
    </div>
  `;
}

/** Inserta (o actualiza) el bloque de información del dispositivo justo antes del botón "Tancar". */
function renderDeviceInfoBlock(container: HTMLElement, data: DeviceInfoInput): void {
  const info: DeviceInfo = Array.isArray(data) ? (data[0] ?? {}) : (data ?? {});

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

  const closeBtn = container.querySelector('#btnTancarFinestra') ?? container.querySelector('[data-role="close-popup"]') ?? Array.from(container.querySelectorAll('button, a')).find((el) => (el.textContent ?? '').trim().toLowerCase().includes('tancar'));

  if (closeBtn && closeBtn.parentElement) {
    closeBtn.parentElement.insertBefore(block, closeBtn);
  } else {
    container.prepend(block);
  }
}

/** Devuelve un DOMRect a partir de un evento o elemento; si no se puede, centra en el viewport. */
function getSourceRect(opener: MouseEvent | HTMLElement | null): DOMRect {
  if (opener && typeof (opener as MouseEvent).type === 'string') {
    const ev = opener as MouseEvent;
    const cur = ev.currentTarget;
    if (cur instanceof Element) return cur.getBoundingClientRect();

    const tgt = ev.target;
    if (tgt instanceof Element) return tgt.getBoundingClientRect();
  }

  if (opener instanceof Element) return opener.getBoundingClientRect();

  const w = 1;
  const h = 1;
  const cx = Math.max(0, window.innerWidth / 2 - w / 2);
  const cy = Math.max(0, window.innerHeight / 2 - h / 2);
  return new DOMRect(cx, cy, w, h);
}

/** Posiciona el popup (fixed) cerca del rectángulo origen y lo encaja dentro del viewport. */
function positionPopupFixed(popup: HTMLElement, sourceRect: DOMRect): void {
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

  let left = sourceRect.left + sourceRect.width / 2 - width / 2;
  left = clamp(margin, left, window.innerWidth - width - margin);

  let top = sourceRect.bottom + margin;
  if (top + height > window.innerHeight - margin) {
    top = sourceRect.top - height - margin;
  }
  top = clamp(margin, top, window.innerHeight - height - margin);

  popup.style.left = `${left}px`;
  popup.style.top = `${top}px`;

  popup.style.visibility = 'visible';

  if (prevDisplay === 'none') {
    // ok
  }
}

function setBtnState(btn: HTMLButtonElement, opts: { text: string; disabled: boolean; klass: 'secondary' | 'warning' | 'success' | 'danger' }): void {
  btn.textContent = opts.text;
  btn.disabled = opts.disabled;
  btn.style.cursor = opts.disabled ? 'not-allowed' : 'pointer';
  btn.style.opacity = opts.disabled ? '0.7' : '1';
  btn.classList.remove('btn-secondary', 'btn-warning', 'btn-success', 'btn-danger');
  btn.classList.add(`btn-${opts.klass}`);
}

/** (Re)engancha listeners al markup actual. Se llama en cada open, pero sin duplicar eventos. */
function bindPopupHandlers(container: HTMLElement, reservaId: string): void {
  const btnConfirm = container.querySelector('#enlace1');
  if (btnConfirm instanceof HTMLButtonElement) {
    // clonar para evitar duplicados
    const clone = btnConfirm.cloneNode(true) as HTMLButtonElement;
    btnConfirm.parentNode?.replaceChild(clone, btnConfirm);

    setBtnState(clone, { text: 'Enviar confirmació email', disabled: false, klass: 'secondary' });

    clone.addEventListener('click', async (ev: MouseEvent) => {
      ev.preventDefault();

      setBtnState(clone, { text: 'Enviant...', disabled: true, klass: 'warning' });

      try {
        const r = await enviarConfirmacioReserva(reservaId);
        setBtnState(clone, { text: 'Email enviat! (Reenviar)', disabled: false, klass: 'success' });
        // si quieres: console.log(r.message);
        void r;
      } catch (err) {
        const msg = err instanceof Error ? err.message : 'Error enviant email';
        setBtnState(clone, { text: 'Error enviant (Reintentar)', disabled: false, klass: 'danger' });
        console.error(msg);
      }
    });

    // Cancel·lar reserva (solo visible si aplica la regla)
    const aCancelar = container.querySelector('#enlace4');
    if (aCancelar instanceof HTMLAnchorElement) {
      const clone = aCancelar.cloneNode(true) as HTMLAnchorElement;
      aCancelar.parentNode?.replaceChild(clone, aCancelar);

      clone.href = '#';

      clone.addEventListener('click', async (ev: MouseEvent) => {
        ev.preventDefault();

        const ok = confirm('Vols cancel·lar aquesta reserva?');
        if (!ok) return;

        // UI loading
        clone.classList.remove('btn-secondary', 'btn-success', 'btn-danger');
        clone.classList.add('btn-warning');
        clone.textContent = 'Cancel·lant...';
        clone.style.pointerEvents = 'none';
        clone.style.opacity = '0.8';

        try {
          const msg = await cancelarReserva(reservaId);

          // UI success
          clone.classList.remove('btn-warning', 'btn-danger');
          clone.classList.add('btn-success');
          clone.textContent = 'Reserva cancel·lada';

          alert(msg);

          // ✅ Avisar a la tabla para refrescar sin acoplar módulos
          window.dispatchEvent(new CustomEvent('reserva:changed', { detail: { id: reservaId, action: 'cancelada' } }));

          // Cerrar popup
          tancarFinestra();
        } catch (err) {
          const msg = err instanceof Error ? err.message : 'Error cancel·lant reserva';

          clone.classList.remove('btn-warning', 'btn-success');
          clone.classList.add('btn-danger');
          clone.textContent = 'Error (Reintentar)';
          clone.style.pointerEvents = '';
          clone.style.opacity = '1';

          alert(msg);
          console.error(msg);
        }
      });
    }
  }

  const btnFactura = container.querySelector('#enlace2');
  if (btnFactura instanceof HTMLButtonElement) {
    const clone = btnFactura.cloneNode(true) as HTMLButtonElement;
    btnFactura.parentNode?.replaceChild(clone, btnFactura);

    setBtnState(clone, { text: 'Enviar factura', disabled: false, klass: 'secondary' });

    clone.addEventListener('click', async (ev: MouseEvent) => {
      ev.preventDefault();

      setBtnState(clone, { text: 'Enviando...', disabled: true, klass: 'warning' });

      try {
        const response = await fetch(`${apiUrl}/factures/send/?type=emitir-factura-y-enviar`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ reserva_id: reservaId }),
        });

        const data: unknown = await response.json();
        const d = data as { status?: string; message?: string };

        if (d.status === 'success') {
          setBtnState(clone, { text: 'Factura enviada', disabled: false, klass: 'success' });
        } else {
          throw new Error(d.message ?? 'Error enviando factura');
        }
      } catch (error) {
        const msg = error instanceof Error ? error.message : 'Error enviando factura';
        setBtnState(clone, { text: 'Error enviando (Reintentar)', disabled: false, klass: 'danger' });
        console.error(msg);
      }
    });
  }

  // Cerrar (por si no usas delegación global)
  const btnClose = container.querySelector('.tancar-finestra-btn');
  if (btnClose instanceof HTMLButtonElement) {
    const clone = btnClose.cloneNode(true) as HTMLButtonElement;
    btnClose.parentNode?.replaceChild(clone, btnClose);
    clone.addEventListener('click', (ev: MouseEvent) => {
      ev.preventDefault();
      tancarFinestra();
    });
  }
}

function updateLinks(container: HTMLElement, reservaId: string): void {
  const urlWeb = window.location.origin + '/control';

  const aMod = container.querySelector('#enlace3');
  if (aMod instanceof HTMLAnchorElement) {
    aMod.href = `${urlWeb}/reserva/modificar/reserva/${reservaId}`;
  }

  // ⛔ NO tocar enlace4 aquí (ahora es acción, no navegación)
}

/** API pública: abre popup (mantiene firma compatible con tu código actual). */
export function obrirFinestra(opener: MouseEvent | HTMLElement | null, id: string, deviceInfo?: DeviceInfoInput, estadoReserva?: string | null): void {
  const ventana = ensurePopupContainer();
  if (!ventana) return;

  // 1) crear markup (antes estaba en PHP)
  ensurePopupMarkup(ventana);
  applyVisibilityRules(ventana, estadoReserva);

  // 2) actualizar links + handlers (sin duplicar)
  updateLinks(ventana, id);
  bindPopupHandlers(ventana, id);

  // 3) device info
  renderDeviceInfoBlock(ventana, deviceInfo);

  // 4) posicionar + mostrar
  const srcRect = getSourceRect(opener);
  positionPopupFixed(ventana, srcRect);
  ventana.style.display = 'block';
}

/** API pública: cerrar */
export function tancarFinestra(): void {
  const ventana = ensurePopupContainer();
  if (ventana) ventana.style.display = 'none';
}

/** Helper opcional: inicializa cierre por click fuera / ESC, si quieres. */
export function initPopupReservaUX(): void {
  // ESC para cerrar
  document.addEventListener('keydown', (ev: KeyboardEvent) => {
    if (ev.key === 'Escape') tancarFinestra();
  });

  // Click fuera para cerrar (si el click no está dentro del popup)
  document.addEventListener('click', (ev: MouseEvent) => {
    const ventana = ensurePopupContainer();
    if (!ventana) return;
    if (ventana.style.display !== 'block') return;

    const target = ev.target;
    if (!(target instanceof Node)) return;

    // si clicas dentro del popup o en el botón que lo abre, no cierres
    if (ventana.contains(target)) return;
    if (isHTMLElement(target) && target.closest('.obrir-finestra-btn')) return;

    // Si te molesta, quítalo
    // tancarFinestra();
  });
}

type ApiSimple = { status?: string; message?: string; code?: string };

const CANCEL_RESERVA_ENDPOINT = `${apiUrl}/intranet/cancelar-reserves/post`; 

async function cancelarReserva(reservaId: string): Promise<string> {
  const res = await fetch(CANCEL_RESERVA_ENDPOINT, {
    method: 'POST',
    credentials: 'include',
    headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
    body: JSON.stringify({ reserva_id: reservaId }),
  });

  const json: unknown = await res.json();
  const data = json as ApiSimple;

  if (!res.ok || data.status !== 'success') {
    throw new Error(data.message || data.code || `Error cancelando (HTTP ${res.status})`);
  }

  return data.message || 'Reserva cancel·lada correctament';
}
