// src/intranet/facturacio/llistat.ts

declare global {
  interface Window {
    APP_WEB?: string;
  }
}

type IntegrityIssue = {
  factura_id: number;
  serie: string;
  numero: string;
  motivo: string;
  posicio?: number | null;
  hash_guardado?: string;
  hash_esperat?: string;
  hash_anterior_guardado?: string;
  hash_anterior_esperat?: string;
  posicion?: number;
};

type IntegrityData = {
  status: 'ok' | 'error';
  total_facturas: number;
  facturas_corruptas: number;
  issues: IntegrityIssue[];
  message?: string;
};

type IntegrityApiResponse = {
  success: boolean;
  data?: IntegrityData;
  error?: string;
};

const APP_WEB_BASE = window.APP_WEB ?? 'https://finguer.com';
const API_URL = `${APP_WEB_BASE}/api/factures/get/`;
const API_POST_URL = `${APP_WEB_BASE}/api/factures/post/`;

type FacturaListado = {
  id: number; // factura_id
  serie: string;
  numero: string;
  numeroVisible: string;
  fechaEmision: string;
  cliente: string;
  nif: string;
  email: string;
  subtotal: number;
  iva: number;
  total: number;
  estado: string;
  reserva_id: number | string;
};

type ApiResponse = {
  success: boolean;
  page: number;
  perPage: number;
  total: number;
  totalPages: number;
  search: string;
  data: FacturaListado[];
};

type EmitirFacturaApiSuccess = {
  status: 'success';
  data: {
    pdf_url: string;
  };
};

type EmitirFacturaApiError = {
  status: 'error' | string;
  message?: string;
  error?: string;
};

type EmitirFacturaApiResponse = EmitirFacturaApiSuccess | EmitirFacturaApiError;

function isEmitirFacturaSuccess(x: EmitirFacturaApiResponse): x is EmitirFacturaApiSuccess {
  return typeof x === 'object' && x !== null && 'status' in x && (x as { status?: unknown }).status === 'success' && 'data' in x && typeof (x as { data?: unknown }).data === 'object' && (x as { data?: { pdf_url?: unknown } }).data?.pdf_url !== undefined && typeof (x as { data?: { pdf_url?: unknown } }).data?.pdf_url === 'string';
}

async function emitirFacturaAjaxJson(reservaId: string): Promise<string> {
  const resp = await fetch(`/api/factures/post/?type=emitir-factura`, {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    credentials: 'include',
    body: JSON.stringify({ reserva_id: reservaId }),
  });

  if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

  const data: EmitirFacturaApiResponse = await resp.json();

  if (!isEmitirFacturaSuccess(data)) {
    throw new Error(data.message ?? data.error ?? 'Error al generar la factura');
  }

  const pdfUrl = data.data.pdf_url?.trim();
  if (!pdfUrl) throw new Error('No se pudo generar la URL del PDF');

  return pdfUrl;
}

// ========= Emitir factura (POST) tipado (sin any) =========

type EmitirFacturaOk = { success: true; factura_id: number } | { success: true; data: { factura_id: number } };

type EmitirFacturaErr = { success: false; error?: string; message?: string };

type EmitirFacturaResponse = EmitirFacturaOk | EmitirFacturaErr;

function isOkWithFacturaId(x: EmitirFacturaResponse): x is EmitirFacturaOk {
  if (!x.success) return false;

  if ('factura_id' in x) {
    return typeof x.factura_id === 'number' && x.factura_id > 0;
  }

  if ('data' in x) {
    const d = x.data;
    return typeof d === 'object' && d !== null && 'factura_id' in d && typeof d.factura_id === 'number' && d.factura_id > 0;
  }

  return false;
}

async function emitirFacturaAjax(reservaId: number): Promise<number> {
  const url = `${API_POST_URL}?type=emitir-factura`;

  const body = new URLSearchParams();
  body.set('reserva_id', String(reservaId));

  const resp = await fetch(url, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/x-www-form-urlencoded;charset=UTF-8',
    },
    body,
    credentials: 'include',
  });

  if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

  const json: EmitirFacturaResponse = await resp.json();

  if (!json.success) {
    throw new Error(json.error ?? json.message ?? 'Error emitint factura');
  }

  if (!isOkWithFacturaId(json)) {
    throw new Error('Resposta OK però manca factura_id');
  }

  const facturaId = 'factura_id' in json ? json.factura_id : json.data.factura_id;
  return facturaId;
}

// =========================================================

export function initTaulaFacturacio(): void {
  const container = document.getElementById('contenidorTaulaFacturacio');
  if (!container) return;

  // ----- UI base -----
  const searchRow = document.createElement('div');
  searchRow.className = 'row mb-3';

  searchRow.innerHTML = `
    <div class="col-md-4">
      <label for="facturesSearch" class="form-label">Cercar (número, client, NIF, email)</label>
      <input type="text" id="facturesSearch" class="form-control" placeholder="Ex: 2025/00012, NIF, email...">
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="button" id="facturesSearchBtn" class="btn btn-primary w-100">Cercar</button>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="button" id="facturesResetBtn" class="btn btn-secondary w-100">Netejar</button>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="button" id="facturesCsvBtn" class="btn btn-success w-100">Exportar CSV</button>
    </div>
    <div class="col-md-2 d-flex align-items-end">
      <button type="button" id="facturesIntegrityBtn" class="btn btn-outline-warning w-100">Verificar integritat</button>
    </div>
    <div class="col-md-12 mt-2 d-flex justify-content-between">
      <div id="facturesSummary" class="small text-muted"></div>
      <div id="facturesIntegrityResult" class="small"></div>
    </div>
  `;

  const tableWrapper = document.createElement('div');
  tableWrapper.className = 'table-responsive';

  const table = document.createElement('table');
  table.className = 'table table-striped table-sm align-middle mb-0';

  const thead = document.createElement('thead');
  thead.className = 'table-dark';
  thead.innerHTML = `
    <tr>
      <th>Sèrie / Número</th>
      <th>Data emissió</th>
      <th>Client</th>
      <th>NIF</th>
      <th>Email</th>
      <th class="text-end">Subtotal</th>
      <th class="text-end">IVA</th>
      <th class="text-end">Total</th>
      <th>Estat</th>
      <th>Historial</th>
      <th>Factura PDF</th>
      <th>Enviar email</th>
    </tr>
  `;

  const tbody = document.createElement('tbody');
  table.appendChild(thead);
  table.appendChild(tbody);
  tableWrapper.appendChild(table);

  const paginationWrapper = document.createElement('nav');
  paginationWrapper.className = 'mt-3';
  const paginationUl = document.createElement('ul');
  paginationUl.className = 'pagination justify-content-center mb-0';
  paginationWrapper.appendChild(paginationUl);

  container.appendChild(searchRow);
  container.appendChild(tableWrapper);
  container.appendChild(paginationWrapper);

  const searchInput = searchRow.querySelector('#facturesSearch') as HTMLInputElement;
  const searchBtn = searchRow.querySelector('#facturesSearchBtn') as HTMLButtonElement;
  const resetBtn = searchRow.querySelector('#facturesResetBtn') as HTMLButtonElement;
  const summaryDiv = searchRow.querySelector('#facturesSummary') as HTMLDivElement;
  const csvBtn = searchRow.querySelector('#facturesCsvBtn') as HTMLButtonElement;
  const integrityBtn = searchRow.querySelector('#facturesIntegrityBtn') as HTMLButtonElement;
  const integrityResultDiv = searchRow.querySelector('#facturesIntegrityResult') as HTMLDivElement;

  tbody.addEventListener('click', async (e: MouseEvent) => {
    const target = e.target as HTMLElement | null;
    const btn = target?.closest<HTMLButtonElement>('.js-emitir-factura');
    if (!btn) return;

    e.preventDefault();

    const reservaId = btn.getAttribute('data-reserva-id');
    if (!reservaId) return;

    const oldText = btn.textContent ?? '';
    btn.disabled = true;
    btn.textContent = 'Generant...';

    try {
      const pdfUrl = await emitirFacturaAjaxJson(reservaId);
      window.open(pdfUrl, '_blank', 'noopener');
    } catch (err) {
      console.error('Error al generar el PDF:', err);
      alert('Hubo un error al generar la factura. Intenta de nuevo.');
    } finally {
      btn.disabled = false;
      btn.textContent = oldText;
    }
  });

  // ------- VERIFICACION FACTURAS HASH -----
  async function verificarIntegritat(): Promise<void> {
    integrityResultDiv.innerHTML = `<span class="text-info">Verificant integritat de les factures...</span>`;

    try {
      const params = new URLSearchParams();
      params.set('type', 'facturacioVerificarIntegridad');

      const response = await fetch(`${API_URL}?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'include',
      });

      if (!response.ok) throw new Error('Error HTTP ' + response.status);

      const json = (await response.json()) as IntegrityApiResponse;

      if (!json.success || !json.data) {
        integrityResultDiv.innerHTML = `<span class="text-danger">Error verificant la integritat de les factures.</span>`;
        console.error('Error integritat:', json.error);
        return;
      }

      const data = json.data;

      if (data.status === 'ok') {
        integrityResultDiv.innerHTML = `
          <span class="text-success">
            Integritat correcta: ${data.total_facturas} factures, cap manipulació detectada.
          </span>
        `;
      } else {
        const corruptes = data.facturas_corruptas;
        let html = `
          <span class="text-danger">
            ALERTA: S'han detectat possibles anomalies en ${corruptes} factures
            (de ${data.total_facturas}).
          </span>
        `;

        if (data.issues.length > 0) {
          const primer = data.issues[0];
          const pos = primer.posicion ?? primer.posicio ?? null;
          html += `
            <br>
            <span class="text-danger">
              Ex: factura ID ${primer.factura_id} (${primer.serie}/${primer.numero})${pos !== null ? `, posició ${pos}` : ''} - ${primer.motivo}
            </span>
          `;
          console.warn('Detall anomalies integritat:', data.issues);
        }

        integrityResultDiv.innerHTML = html;
      }
    } catch (error) {
      console.error(error);
      integrityResultDiv.innerHTML = `<span class="text-danger">Error inesperat verificant la integritat.</span>`;
    }
  }

  let currentPage = 1;
  const perPage = 50;
  let currentSearch = '';

  csvBtn.addEventListener('click', () => {
    const params = new URLSearchParams();
    params.set('type', 'facturacioLlistat');
    params.set('export', 'csv');
    if (currentSearch.trim() !== '') params.set('q', currentSearch.trim());
    window.location.href = `${API_URL}?${params.toString()}`;
  });

  // ----- Fetch + render -----
  async function carregarFactures(page: number = 1): Promise<void> {
    const params = new URLSearchParams();
    params.set('type', 'facturacioLlistat');
    params.set('page', page.toString());
    params.set('per_page', perPage.toString());
    if (currentSearch.trim() !== '') params.set('q', currentSearch.trim());

    tbody.innerHTML = `
      <tr>
        <td colspan="12" class="text-center text-muted">Carregant factures...</td>
      </tr>
    `;

    try {
      const response = await fetch(`${API_URL}?${params.toString()}`, {
        headers: { Accept: 'application/json' },
        credentials: 'include',
      });

      if (!response.ok) throw new Error('Error HTTP ' + response.status);

      const json = (await response.json()) as ApiResponse;
      if (!json.success) throw new Error('Resposta API incorrecta');

      currentPage = json.page;
      pintarTaula(json);
      pintarPaginacio(json);
      pintarResum(json);
    } catch (error: unknown) {
      console.error(error);
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center text-danger">Error carregant les factures.</td>
        </tr>
      `;
      paginationUl.innerHTML = '';
      summaryDiv.textContent = '';
    }
  }

  function pintarTaula(data: ApiResponse): void {
    const rows = data.data;

    if (!rows.length) {
      tbody.innerHTML = `
        <tr>
          <td colspan="12" class="text-center text-muted">No s'han trobat factures.</td>
        </tr>
      `;
      return;
    }

    tbody.innerHTML = '';

    rows.forEach((f: FacturaListado) => {
      const tr = document.createElement('tr');

      const numeroVisible = `${f.serie}/${f.numero}`;
      const fechaEmision_date = new Date(f.fechaEmision);
      const opcionesFormato: Intl.DateTimeFormatOptions = {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
      };
      const fechaEmision_format = fechaEmision_date.toLocaleDateString('es-ES', opcionesFormato);

      const urlHistorialLogs = `${APP_WEB_BASE}/control/facturacio/historial/${f.id}`;
      const urlEnviarEmail = `${APP_WEB_BASE}/intranet/factura/enviar/${f.id}`;

      const reservaIdNum = Number(f.reserva_id);
      const reservaIdAttr = Number.isFinite(reservaIdNum) ? String(reservaIdNum) : '';

      tr.innerHTML = `
        <td>${escapeHtml(numeroVisible)}</td>
        <td>${escapeHtml(fechaEmision_format)}</td>
        <td>${escapeHtml(f.cliente)}</td>
        <td>${escapeHtml(f.nif)}</td>
        <td>${escapeHtml(f.email)}</td>
        <td class="text-end">${formatEuro(f.subtotal)}</td>
        <td class="text-end">${formatEuro(f.iva)}</td>
        <td class="text-end"><strong>${formatEuro(f.total)}</strong></td>
        <td>${escapeHtml(f.estado)}</td>
        <td><a href="${urlHistorialLogs}" class="btn btn-outline-secondary btn-sm">Veure logs</a></td>
        <td>
          ${
            f.numero && f.serie && reservaIdAttr
              ? `<button
                   type="button"
                   class="btn btn-outline-secondary btn-sm js-emitir-factura"
                   data-reserva-id="${escapeHtml(reservaIdAttr)}"
                 >
                   ${escapeHtml(f.serie)}/${escapeHtml(f.numero)}
                 </button>`
              : '-'
          }
        </td>
        <td>
          <a href="${urlEnviarEmail}" class="btn btn-sm btn-outline-primary">Enviar</a>
        </td>
      `;

      tbody.appendChild(tr);
    });
  }

  // ✅ Event delegation: click en el botón "Factura PDF" (POST emitir-factura)
  tbody.addEventListener('click', async (ev: MouseEvent) => {
    const target = ev.target as HTMLElement | null;
    const btn = target?.closest<HTMLButtonElement>('.js-emitir-factura');
    if (!btn) return;

    const ridStr = btn.getAttribute('data-reserva-id') ?? '';
    const rid = Number(ridStr);

    if (!Number.isFinite(rid) || rid <= 0) {
      console.error('reserva_id inválido:', ridStr);
      alert('reserva_id inválid (mira consola).');
      return;
    }

    const oldText = btn.textContent ?? '';
    btn.disabled = true;
    btn.textContent = 'Generant...';

    try {
      const facturaId = await emitirFacturaAjax(rid);
      const pdfUrl = `${APP_WEB_BASE}/api/factures/pdf/?type=factura-pdf&id=${facturaId}`;
      window.open(pdfUrl, '_blank', 'noopener');
    } catch (e) {
      console.error(e);
      alert('No s’ha pogut emetre/obrir la factura. Mira la consola/logs.');
    } finally {
      btn.disabled = false;
      btn.textContent = oldText;
    }
  });

  function pintarPaginacio(data: ApiResponse): void {
    const { page, totalPages } = data;

    paginationUl.innerHTML = '';
    if (totalPages <= 1) return;

    const createPageItem = (label: string, targetPage: number, disabled: boolean, active: boolean = false): HTMLLIElement => {
      const li = document.createElement('li');
      li.className = 'page-item';
      if (disabled) li.classList.add('disabled');
      if (active) li.classList.add('active');

      const btn = document.createElement('button');
      btn.type = 'button';
      btn.className = 'page-link';
      btn.textContent = label;

      if (!disabled) {
        btn.addEventListener('click', () => {
          if (targetPage !== page) carregarFactures(targetPage);
        });
      }

      li.appendChild(btn);
      return li;
    };

    paginationUl.appendChild(createPageItem('«', page - 1, page <= 1));

    const start = Math.max(1, page - 2);
    const end = Math.min(totalPages, page + 2);

    for (let p = start; p <= end; p++) {
      paginationUl.appendChild(createPageItem(p.toString(), p, false, p === page));
    }

    paginationUl.appendChild(createPageItem('»', page + 1, page >= totalPages));
  }

  function pintarResum(data: ApiResponse): void {
    const { page, perPage, total } = data;
    if (!total) {
      summaryDiv.textContent = '0 factures';
      return;
    }
    const start = (page - 1) * perPage + 1;
    const end = Math.min(page * perPage, total);
    summaryDiv.textContent = `Mostrant ${start}-${end} de ${total} factures`;
  }

  // Helpers
  function escapeHtml(str: string | null | undefined): string {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
  }

  function formatEuro(value: number): string {
    return value.toFixed(2).replace('.', ',') + ' €';
  }

  // Eventos buscador
  searchBtn.addEventListener('click', () => {
    currentSearch = searchInput.value;
    carregarFactures(1);
  });

  resetBtn.addEventListener('click', () => {
    searchInput.value = '';
    currentSearch = '';
    carregarFactures(1);
  });

  searchInput.addEventListener('keyup', (e: KeyboardEvent) => {
    if (e.key === 'Enter') {
      currentSearch = searchInput.value;
      carregarFactures(1);
    }
  });

  integrityBtn.addEventListener('click', () => {
    verificarIntegritat();
  });

  // Carga inicial
  carregarFactures(currentPage);
}
