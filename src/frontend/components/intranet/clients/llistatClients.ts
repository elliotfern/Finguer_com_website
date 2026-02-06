// ==============================
// Finguer Intranet - Users table
// Server-side filtering + pagination (Option A)
// ==============================

import { isAdmin } from '../auth/store';

export type UserRole = 'cliente' | 'administrador' | 'cliente_anual' | 'trabajador';
export type ActiveRole = UserRole | 'tots';

export interface ApiUserRow {
  uuid: string;
  nombre: string;
  email: string;
  telefono?: string;
  tipo_rol: string; // backend: admin | trabajador | cliente | cliente_anual
}

interface Vp2Ok<T> {
  status: 'success';
  message?: string;
  data: T;
}

interface Vp2Err {
  status: 'error';
  message?: string;
  error?: unknown;
  data?: unknown;
}

type ApiResponse<T> = Vp2Ok<T> | Vp2Err;

interface ListUsersData {
  rows: ApiUserRow[];
  total?: number;
  limit?: number;
  offset?: number;
  q?: string;
  role?: string | null;
  hasRows?: boolean;
}

// ------------------------------
// Config
// ------------------------------
const API_LIST_URL = '/api/usuaris/get/?type=list';
const CONTAINER_ID = 'contenidorTaulaClients';

const ROLE_BUTTONS: Array<{ key: UserRole; label: string }> = [
  { key: 'cliente', label: 'Cliente' },
  { key: 'administrador', label: 'Administrador' },
  { key: 'cliente_anual', label: 'Cliente anual' },
  { key: 'trabajador', label: 'Trabajador' },
];

// ------------------------------
// State
// ------------------------------
let rows: ApiUserRow[] = [];
let total = 0;

let activeRole: ActiveRole = 'tots';
let searchText = '';

let limit = 50;
let offset = 0;

let isWired = false;
let fetchController: AbortController | null = null;

let searchDebounceTimer: number | null = null;

// ------------------------------
// Public entry point
// ------------------------------
export async function clientsUsersTable(): Promise<void> {
  const container = document.getElementById(CONTAINER_ID);
  if (!container) {
    console.warn(`[clientsUsersTable] No existe #${CONTAINER_ID}`);
    return;
  }

  container.innerHTML = `<div class="text-muted">Carregant usuaris...</div>`;

  try {
    renderShell(container);

    // eventos UNA sola vez
    if (!isWired) {
      wireEvents(container);
      isWired = true;
    }

    // carga inicial
    await loadAndRender(container);
  } catch (err: unknown) {
    const msg = err instanceof Error ? err.message : 'Error desconegut';
    container.innerHTML = `
      <div class="alert alert-danger">
        Error carregant usuaris. (${escapeHtml(msg)})
      </div>
    `;
  }
}

// ------------------------------
// Load + render
// ------------------------------
async function loadAndRender(container: HTMLElement): Promise<void> {
  setLoading(container, true);

  const apiRole = roleUiToApi(activeRole);
  const data = await fetchUsers({
    role: apiRole ?? undefined,
    q: searchText,
    limit,
    offset,
  });

  rows = data.rows;
  total = data.total ?? 0;
  limit = data.limit ?? limit;
  offset = data.offset ?? offset;

  renderBody(container);
  setLoading(container, false);
}

function setLoading(container: HTMLElement, on: boolean): void {
  const badge = container.querySelector<HTMLSpanElement>('#usersLoading');
  if (!badge) return;
  badge.textContent = on ? 'Carregant…' : '';
}

// ------------------------------
// Fetch
// ------------------------------
type FetchUsersParams = {
  role?: string; // backend role: admin|cliente|cliente_anual|trabajador
  q?: string;
  limit?: number;
  offset?: number;
};

async function fetchUsers(params: FetchUsersParams): Promise<ListUsersData> {
  // abort previous
  if (fetchController) fetchController.abort();
  fetchController = new AbortController();

  const url = new URL(API_LIST_URL, window.location.origin);

  if (params.role) url.searchParams.set('role', params.role);
  if (params.q && params.q.trim() !== '') url.searchParams.set('q', params.q.trim());

  url.searchParams.set('limit', String(params.limit ?? 50));
  url.searchParams.set('offset', String(params.offset ?? 0));

  const res = await fetch(url.toString(), {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
    signal: fetchController.signal,
  });

  if (!res.ok) throw new Error(`HTTP ${res.status}`);

  const json = (await res.json()) as ApiResponse<ListUsersData>;

  if (json.status !== 'success') {
    throw new Error(json.message ?? 'API error');
  }

  const data = json.data ?? { rows: [] };
  if (!Array.isArray(data.rows)) data.rows = [];

  return data;
}

// ------------------------------
// Render
// ------------------------------
function renderShell(container: HTMLElement): void {
  const canAdmin = isAdmin();

  container.innerHTML = `
    <div class="row g-2 align-items-center mb-3">
      <div class="col-12 col-lg-8">
        <div id="roleButtons" class="btn-group flex-wrap" role="group" aria-label="Filtres per rol">
          ${renderRoleButtons()}
        </div>
      </div>

      <div class="col-12 col-lg-4 d-flex gap-2 align-items-center">
        <input
          id="usersSearch"
          type="text"
          class="form-control"
          placeholder="Cerca per nom, email, tel..."
          value="${escapeHtml(searchText)}"
        >
        <span id="usersLoading" class="text-muted small"></span>
      </div>
    </div>

    <div class="table-responsive">
      <table class="table table-striped table-hover table-bordered align-middle mb-0">
        <thead class="table-dark">
          <tr>
            <th style="min-width: 220px;">Nom</th>
            <th style="min-width: 240px;">Email</th>
            <th style="min-width: 160px;">Telèfon</th>
            <th style="min-width: 140px;">Rol</th>
            <th style="min-width: 140px;">Reserves</th>
            ${canAdmin ? `<th style="min-width: 110px;">Modifica</th>` : ``}
            ${canAdmin ? `<th style="min-width: 110px;">Elimina</th>` : ``}
          </tr>
        </thead>
        <tbody id="usersTbody"></tbody>
      </table>
    </div>

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mt-2">
      <div id="usersCount" class="text-muted small"></div>

      <div class="d-flex align-items-center gap-2">
        <button id="usersPrev" type="button" class="btn btn-sm btn-outline-secondary">← Anterior</button>
        <div id="usersPageInfo" class="text-muted small"></div>
        <button id="usersNext" type="button" class="btn btn-sm btn-outline-secondary">Següent →</button>
      </div>
    </div>
  `;
}

function renderBody(container: HTMLElement): void {
  const tbody = container.querySelector<HTMLTableSectionElement>('#usersTbody');
  const count = container.querySelector<HTMLDivElement>('#usersCount');
  const pageInfo = container.querySelector<HTMLDivElement>('#usersPageInfo');
  const prevBtn = container.querySelector<HTMLButtonElement>('#usersPrev');
  const nextBtn = container.querySelector<HTMLButtonElement>('#usersNext');
  const roleButtons = container.querySelector<HTMLDivElement>('#roleButtons');

  if (!tbody || !count || !pageInfo || !prevBtn || !nextBtn || !roleButtons) return;

  // update buttons active state
  roleButtons.innerHTML = renderRoleButtons();

  // rows
  tbody.innerHTML = renderRows(rows);

  // counters
  const shownFrom = total === 0 ? 0 : offset + 1;
  const shownTo = offset + rows.length;

  count.innerHTML = `Mostrant <strong>${shownFrom}</strong>-<strong>${shownTo}</strong> de <strong>${total}</strong>`;

  // pagination
  const currentPage = total === 0 ? 0 : Math.floor(offset / limit) + 1;
  const totalPages = total === 0 ? 0 : Math.ceil(total / limit);

  pageInfo.innerHTML = total === 0 ? `Pàgina 0/0` : `Pàgina <strong>${currentPage}</strong>/<strong>${totalPages}</strong>`;

  const hasPrev = offset > 0;
  const hasNext = offset + rows.length < total;

  prevBtn.disabled = !hasPrev;
  nextBtn.disabled = !hasNext;
}

function renderRoleButtons(): string {
  const btn = (key: ActiveRole, label: string): string => {
    const activeClass = key === activeRole ? 'btn-primary' : 'btn-outline-primary';
    return `<button type="button" class="btn ${activeClass}" data-role="${escapeHtml(key)}">${escapeHtml(label)}</button>`;
  };

  return [btn('tots', 'Tots'), ...ROLE_BUTTONS.map((r) => btn(r.key, r.label))].join('');
}

function renderRows(rowsToRender: ApiUserRow[]): string {
  if (!rowsToRender.length) {
    return `<tr><td colspan="7" class="text-center text-muted py-4">Sense resultats</td></tr>`;
  }

  const canAdmin = isAdmin();

   return rowsToRender
    .map((r) => {
      const uuid = escapeHtml(r.uuid);
      const nombre = escapeHtml(r.nombre);
      const email = escapeHtml(r.email);
      const tel = escapeHtml(r.telefono ?? '');
      const roleBadge = escapeHtml(String(normalizeRoleUiLabel(r.tipo_rol)));

      const tdEdit = canAdmin
        ? `<td class="text-center">
             <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-uuid="${uuid}">
               Modifica
             </button>
           </td>`
        : ``;

      const tdDelete = canAdmin
        ? `<td class="text-center">
             <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-uuid="${uuid}">
               Elimina
             </button>
           </td>`
        : ``;

      return `
        <tr data-uuid="${uuid}">
          <td>${nombre}</td>
          <td><a href="mailto:${email}">${email}</a></td>
          <td>${tel}</td>
          <td><span class="badge text-bg-secondary">${roleBadge}</span></td>

          <td class="text-center">
            <button type="button" class="btn btn-sm btn-outline-dark" data-action="reservas" data-email="${email}">
              Veure reserves
            </button>
          </td>

          ${tdEdit}
          ${tdDelete}
        </tr>
      `;
    })
    .join('');
}

// ------------------------------
// Events
// ------------------------------
function wireEvents(container: HTMLElement): void {
  // Delegación de eventos
  container.addEventListener('click', async (ev: MouseEvent) => {
    const target = ev.target as Element | null;
    if (!target) return;

    // role button
    const roleBtn = target.closest<HTMLButtonElement>('button[data-role]');
    if (roleBtn) {
      const role = (roleBtn.dataset.role ?? 'tots') as ActiveRole;

      // reset paging on role change
      activeRole = role;
      offset = 0;

      await safeLoad(container);
      return;
    }

    // pagination
    const prevBtn = target.closest<HTMLButtonElement>('#usersPrev');
    if (prevBtn) {
      if (offset <= 0) return;
      offset = Math.max(0, offset - limit);
      await safeLoad(container);
      return;
    }

    const nextBtn = target.closest<HTMLButtonElement>('#usersNext');
    if (nextBtn) {
      offset = offset + limit;
      await safeLoad(container);
      return;
    }

    // row actions
    const actionBtn = target.closest<HTMLButtonElement>('button[data-action]');
    if (actionBtn) {
      const action = actionBtn.dataset.action;
      if (!action) return;

      if (action === 'reservas') {
        const email = actionBtn.dataset.email;
        if (!email) return;
        handleAction(action, email);
        return;
      }

      const uuid = actionBtn.dataset.uuid;
      if (!uuid) return;
      handleAction(action, uuid);
      return;
    }
  });

  // search input (server-side with debounce)
  const search = container.querySelector<HTMLInputElement>('#usersSearch');
  if (search) {
    search.addEventListener('input', () => {
      searchText = search.value ?? '';

      // reset paging on search
      offset = 0;

      if (searchDebounceTimer) window.clearTimeout(searchDebounceTimer);
      searchDebounceTimer = window.setTimeout(() => {
        void safeLoad(container);
      }, 250);
    });
  }
}

async function safeLoad(container: HTMLElement): Promise<void> {
  try {
    await loadAndRender(container);
  } catch (e: unknown) {
    // Si abortamos por una nueva búsqueda, ignoramos
    if (e instanceof DOMException && e.name === 'AbortError') return;

    const msg = e instanceof Error ? e.message : 'Error desconegut';
    const tbody = container.querySelector<HTMLTableSectionElement>('#usersTbody');
    const count = container.querySelector<HTMLDivElement>('#usersCount');
    if (tbody) tbody.innerHTML = `<tr><td colspan="7" class="text-center text-danger py-4">Error carregant dades: ${escapeHtml(msg)}</td></tr>`;
    if (count) count.textContent = '';
  }
}

// ------------------------------
// Actions
// ------------------------------
function handleAction(action: string, value: string): void {

   // Guard: accions restringides
  if ((action === 'edit' || action === 'delete') && !isAdmin()) {
    alert('No tens permisos per fer aquesta acció.');
    return;
  }
  
  switch (action) {
    case 'reservas':
      window.location.href = `/control/usuaris/reserves-client/?email=${encodeURIComponent(value)}`;
      return;

    case 'edit':
      window.location.href = `/control/usuaris/modifica-client/${encodeURIComponent(value)}`;
      return;

    case 'delete':
      if (!confirm('Segur que vols eliminar aquest usuari?')) return;
      console.log('[DELETE usuario]', value);
      alert("Acció pendent: implementar endpoint d'eliminació.");
      return;

    default:
      console.warn('Acció desconeguda:', action, value);
  }
}

// ------------------------------
// Role mapping
// ------------------------------
function roleUiToApi(role: ActiveRole): string | null {
  if (role === 'tots') return null;
  if (role === 'administrador') return 'admin';
  return role; // cliente | cliente_anual | trabajador
}

function normalizeRoleUiLabel(tipoRol: string): UserRole | string {
  const r = (tipoRol || '').trim().toLowerCase();
  if (r === 'admin') return 'administrador';
  if (r === 'administrador') return 'administrador';
  if (r === 'cliente') return 'cliente';
  if (r === 'cliente_anual') return 'cliente_anual';
  if (r === 'trabajador') return 'trabajador';
  return tipoRol;
}

// ------------------------------
// Utils
// ------------------------------
function escapeHtml(input: unknown): string {
  const str = String(input ?? '');
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
