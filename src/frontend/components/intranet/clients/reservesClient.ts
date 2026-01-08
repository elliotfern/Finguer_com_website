// reservesClient.ts

import { API_BASE } from "../../../config/globals";

type Vp2Ok<T> = {
  status: 'success';
  message?: string;
  data: T;
};

type Vp2Err = {
  status: 'error';
  message?: string;
  errors?: string[];
  code?: string;
};

type ApiResponse<T> = Vp2Ok<T> | Vp2Err;

export interface ReservaRow {
  id: number;
  localizador: string;
  estado: string;
  estado_vehiculo: string;
  fecha_reserva: string;      // datetime
  entrada_prevista: string;   // datetime
  salida_prevista: string;    // datetime
  total_calculado?: string | number | null;
  vehiculo?: string | null;
  matricula?: string | null;
  created_at?: string | null;
}

interface ListReservesData {
  email: string;
  limit: number;
  offset: number;
  total: number;
  rows: ReservaRow[];
  hasRows?: boolean;
}

// ------------------------------
// Config
// ------------------------------
const API_LIST_BY_EMAIL_URL = `${API_BASE}/reserves/get/?type=list-by-email`;
const CONTAINER_ID = 'contenidorReservesClient'; // tu div contenedor tabla
const TITLE_ID = 'titolReservesClient';          // tu div título

const DEFAULT_LIMIT = 25;
const MAX_LIMIT = 200;

// ------------------------------
// State
// ------------------------------
let emailFromRoute = '';
let limit = DEFAULT_LIMIT;
let offset = 0;
let total = 0;
let rows: ReservaRow[] = [];

// ------------------------------
// Public entry point
// ------------------------------
export async function reservesClientPage(): Promise<void> {
  const container = document.getElementById(CONTAINER_ID);
  const title = document.getElementById(TITLE_ID);

  if (!container) {
    console.warn(`[reservesClientPage] No existe #${CONTAINER_ID}`);
    return;
  }

  emailFromRoute = getEmailFromPath('/control/reserves-client');
  if (!emailFromRoute) {
    container.innerHTML = `<div class="alert alert-danger">Falta email en la URL.</div>`;
    return;
  }

  if (title) {
    title.innerHTML = `
      <h2>Reserves del client</h2>
      <p class="text-muted mb-0">${escapeHtml(emailFromRoute)}</p>
    `;
  }

  container.innerHTML = `<div class="text-muted">Carregant reserves...</div>`;

  await loadAndRender(container);
}

// ------------------------------
// Load + render
// ------------------------------
async function loadAndRender(container: HTMLElement): Promise<void> {
  try {
    const res = await fetchReservesByEmail(emailFromRoute, limit, offset);
    rows = res.rows;
    total = res.total;

    render(container);
    wirePagination(container);
  } catch (err: unknown) {
    const msg = err instanceof Error ? err.message : 'Error desconegut';
    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant reserves. (${escapeHtml(msg)})
      </div>
    `;
  }
}

// ------------------------------
// Fetch
// ------------------------------
async function fetchReservesByEmail(email: string, limit: number, offset: number): Promise<ListReservesData> {
  const url =
    `${API_LIST_BY_EMAIL_URL}` +
    `&email=${encodeURIComponent(email)}` +
    `&limit=${encodeURIComponent(String(limit))}` +
    `&offset=${encodeURIComponent(String(offset))}`;

  const res = await fetch(url, {
    method: 'GET',
    credentials: 'include', // importante si auth por cookie
    headers: { Accept: 'application/json' },
  });

  if (!res.ok) throw new Error(`HTTP ${res.status}`);

  const json = (await res.json()) as ApiResponse<ListReservesData>;

  if (json.status !== 'success') {
    throw new Error(json.message ?? 'API error');
  }

  return json.data;
}

// ------------------------------
// Render
// ------------------------------
function render(container: HTMLElement): void {
  container.innerHTML = `
    <div class="d-flex justify-content-between align-items-center mb-2">
      <div class="text-muted small">
        Mostrant <strong>${rows.length}</strong> de <strong>${total}</strong>
      </div>

      <div class="d-flex gap-2 align-items-center">
        <label class="small text-muted mb-0">Límit</label>
        <select id="limitSelect" class="form-select form-select-sm" style="width:auto;">
          ${[10, 25, 50, 100, 200].map((n) => `
            <option value="${n}" ${n === limit ? 'selected' : ''}>${n}</option>
          `).join('')}
        </select>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th style="min-width: 120px;">Localitzador</th>
            <th style="min-width: 140px;">Estat</th>
            <th style="min-width: 160px;">Estat vehicle</th>
            <th style="min-width: 170px;">Entrada</th>
            <th style="min-width: 170px;">Sortida</th>
            <th style="min-width: 110px;">Total</th>
            <th style="min-width: 140px;">Vehicle</th>
            <th style="min-width: 120px;">Matrícula</th>
          </tr>
        </thead>
        <tbody>
          ${renderRows(rows)}
        </tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
      <button id="btnPrev" class="btn btn-sm btn-outline-secondary" ${offset <= 0 ? 'disabled' : ''}>Anterior</button>

      <div class="text-muted small">
        Pàgina ${pageNumber(offset, limit)} / ${totalPages(total, limit)}
      </div>

      <button id="btnNext" class="btn btn-sm btn-outline-secondary" ${offset + limit >= total ? 'disabled' : ''}>Següent</button>
    </div>
  `;
}

function renderRows(rows: ReservaRow[]): string {
  if (!rows.length) {
    return `<tr><td colspan="8" class="text-center text-muted py-4">Sense reserves</td></tr>`;
  }

  return rows.map((r) => {
    const localizador = escapeHtml(r.localizador ?? '');
    const estado = escapeHtml(r.estado ?? '');
    const estadoVeh = escapeHtml(r.estado_vehiculo ?? '');

    const entrada = escapeHtml(formatDateTime(r.entrada_prevista));
    const salida = escapeHtml(formatDateTime(r.salida_prevista));

    const total = escapeHtml(formatMoney(r.total_calculado));
    const vehiculo = escapeHtml(r.vehiculo ?? '');
    const matricula = escapeHtml(r.matricula ?? '');

    return `
      <tr>
        <td><code>${localizador}</code></td>
        <td>${badge(estado)}</td>
        <td>${badge(estadoVeh)}</td>
        <td>${entrada}</td>
        <td>${salida}</td>
        <td class="text-end">${total}</td>
        <td>${vehiculo}</td>
        <td><code>${matricula}</code></td>
      </tr>
    `;
  }).join('');
}

// ------------------------------
// Pagination events
// ------------------------------
function wirePagination(container: HTMLElement): void {
  const prev = container.querySelector<HTMLButtonElement>('#btnPrev');
  const next = container.querySelector<HTMLButtonElement>('#btnNext');
  const limitSelect = container.querySelector<HTMLSelectElement>('#limitSelect');

  prev?.addEventListener('click', async () => {
    offset = Math.max(0, offset - limit);
    await loadAndRender(container);
  });

  next?.addEventListener('click', async () => {
    offset = offset + limit;
    await loadAndRender(container);
  });

  limitSelect?.addEventListener('change', async () => {
    const n = Number(limitSelect.value);
    if (Number.isFinite(n)) {
      limit = clamp(n, 1, MAX_LIMIT);
      offset = 0; // reset
      await loadAndRender(container);
    }
  });
}

// ------------------------------
// Helpers
// ------------------------------
function getEmailFromPath(prefix: string): string {
  const path = window.location.pathname.replace(/\/+$/, '');
  const base = prefix.replace(/\/+$/, '');

  if (!path.startsWith(base + '/')) return '';

  const encoded = path.slice(base.length + 1);
  const email = decodeURIComponent(encoded);

  // validación básica (no bloquea +)
  return email.includes('@') ? email : '';
}

function formatDateTime(dt: string | null | undefined): string {
  if (!dt) return '';
  // MySQL DATETIME -> "YYYY-MM-DD HH:MM:SS"
  // Lo dejamos legible sin depender de timezone JS
  return dt.replace('T', ' ').slice(0, 16);
}

function formatMoney(v: string | number | null | undefined): string {
  if (v === null || v === undefined || v === '') return '';
  const n = typeof v === 'number' ? v : Number(v);
  if (!Number.isFinite(n)) return String(v);
  return n.toFixed(2) + ' €';
}

function badge(text: string): string {
  if (!text) return '';
  return `<span class="badge text-bg-secondary">${escapeHtml(text)}</span>`;
}

function totalPages(total: number, limit: number): number {
  if (limit <= 0) return 1;
  return Math.max(1, Math.ceil(total / limit));
}

function pageNumber(offset: number, limit: number): number {
  if (limit <= 0) return 1;
  return Math.floor(offset / limit) + 1;
}

function clamp(n: number, min: number, max: number): number {
  return Math.max(min, Math.min(max, n));
}

function escapeHtml(input: unknown): string {
  const str = String(input ?? '');
  return str
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
