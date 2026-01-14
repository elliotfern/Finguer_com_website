export type UserRole = 'cliente' | 'administrador' | 'cliente_anual' | 'trabajador';

export interface ApiUserRow {
  uuid: string;
  nombre: string;
  email: string;
  telefono?: string;
  tipo_rol: string;
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
let allRows: ApiUserRow[] = [];
let activeRole: UserRole | 'tots' = 'tots';
let searchText = '';

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
    allRows = await fetchUsers();
    renderShell(container); // ✅ solo una vez
    renderBody(container); // ✅ solo tbody
    wireEvents(container); // ✅ eventos una vez
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
// Fetch
// ------------------------------
async function fetchUsers(): Promise<ApiUserRow[]> {
  const res = await fetch(API_LIST_URL, {
    method: 'GET',
    credentials: 'include',
    headers: { Accept: 'application/json' },
  });

  if (!res.ok) throw new Error(`HTTP ${res.status}`);

  const json = (await res.json()) as ApiResponse<ListUsersData>;

  if (json.status !== 'success') {
    throw new Error(json.message ?? 'API error');
  }

  return Array.isArray(json.data?.rows) ? json.data.rows : [];
}

// ------------------------------
// Render
// ------------------------------
function renderShell(container: HTMLElement): void {
  container.innerHTML = `
    <div class="row g-2 align-items-center mb-3">
      <div class="col-12 col-lg-8">
        <div id="roleButtons" class="btn-group flex-wrap" role="group" aria-label="Filtres per rol">
          ${renderRoleButtons()}
        </div>
      </div>

      <div class="col-12 col-lg-4">
        <input
          id="usersSearch"
          type="text"
          class="form-control"
          placeholder="Cerca per nom, email, tel..."
          value="${escapeHtml(searchText)}"
        >
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
            <th style="min-width: 110px;">Modifica</th>
            <th style="min-width: 110px;">Elimina</th>
          </tr>
        </thead>
        <tbody id="usersTbody"></tbody>
      </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-2">
      <div id="usersCount" class="text-muted small"></div>
    </div>
  `;
}

function renderBody(container: HTMLElement): void {
  const tbody = container.querySelector<HTMLTableSectionElement>('#usersTbody');
  const count = container.querySelector<HTMLDivElement>('#usersCount');
  const roleButtons = container.querySelector<HTMLDivElement>('#roleButtons');

  if (!tbody || !count || !roleButtons) return;

  // actualizar botones (activo/inactivo) sin re-render global
  roleButtons.innerHTML = renderRoleButtons();

  const filtered = applyFilters(allRows);
  tbody.innerHTML = renderRows(filtered);

  count.innerHTML = `Mostrant <strong>${filtered.length}</strong> de <strong>${allRows.length}</strong>`;
}

function renderRoleButtons(): string {
  const btn = (key: UserRole | 'tots', label: string): string => {
    const activeClass = key === activeRole ? 'btn-primary' : 'btn-outline-primary';
    return `<button type="button" class="btn ${activeClass}" data-role="${escapeHtml(key)}">${escapeHtml(label)}</button>`;
  };

  return [btn('tots', 'Tots'), ...ROLE_BUTTONS.map((r) => btn(r.key, r.label))].join('');
}

function renderRows(rows: ApiUserRow[]): string {
  if (!rows.length) {
    return `<tr><td colspan="7" class="text-center text-muted py-4">Sense resultats</td></tr>`;
  }

  return rows
    .map((r) => {
      const uuid = escapeHtml(r.uuid);
      const nombre = escapeHtml(r.nombre);
      const email = escapeHtml(r.email);
      const tel = escapeHtml(r.telefono ?? '');
      const role = escapeHtml(String(normalizeRole(r.tipo_rol)));

      return `
      <tr data-uuid="${uuid}">
        <td>${nombre}</td>
        <td><a href="mailto:${email}">${email}</a></td>
        <td>${tel}</td>
        <td><span class="badge text-bg-secondary">${role}</span></td>

        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-dark" data-action="reservas" data-email="${email}">Veure reserves</button>
        </td>

        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-primary" data-action="edit" data-uuid="${uuid}">Modifica</button>
        </td>

        <td class="text-center">
          <button type="button" class="btn btn-sm btn-outline-danger" data-action="delete" data-uuid="${uuid}">Elimina</button>
        </td>
      </tr>
    `;
    })
    .join('');
}

// ------------------------------
// Events
// ------------------------------
function wireEvents(container: HTMLElement): void {
  // Delegación de eventos: NO re-registrar mil listeners
  container.addEventListener('click', (ev: MouseEvent) => {
    const target = ev.target as Element | null;
    if (!target) return;

    const roleBtn = target.closest<HTMLButtonElement>('button[data-role]');
    if (roleBtn) {
      const role = (roleBtn.dataset.role ?? 'tots') as UserRole | 'tots';
      activeRole = role;
      renderBody(container);
      return;
    }

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

  const search = container.querySelector<HTMLInputElement>('#usersSearch');
  if (search) {
    search.addEventListener('input', () => {
      searchText = search.value ?? '';
      renderBody(container); // ✅ solo tbody
    });
  }
}

// ------------------------------
// Actions
// ------------------------------
function handleAction(action: string, value: string): void {
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
// Filtering
// ------------------------------
function applyFilters(rows: ApiUserRow[]): ApiUserRow[] {
  return rows.filter((r) => {
    const role = normalizeRole(r.tipo_rol);
    const roleOk = activeRole === 'tots' ? true : role === activeRole;
    const searchOk = rowMatchesSearch(r, searchText);
    return roleOk && searchOk;
  });
}

function rowMatchesSearch(row: ApiUserRow, q: string): boolean {
  if (!q) return true;
  const hay = `${row.nombre ?? ''} ${row.email ?? ''} ${row.telefono ?? ''} ${row.tipo_rol ?? ''}`.toLowerCase();
  return hay.includes(q.toLowerCase());
}

// ------------------------------
// Utils
// ------------------------------
function normalizeRole(tipoRol: string): UserRole | string {
  const r = (tipoRol || '').trim().toLowerCase();
  if (r === 'admin') return 'administrador';
  if (r === 'administrador') return 'administrador';
  if (r === 'cliente') return 'cliente';
  if (r === 'cliente_anual') return 'cliente_anual';
  if (r === 'trabajador') return 'trabajador';
  return tipoRol;
}

function escapeHtml(input: unknown): string {
  const str = String(input ?? '');
  return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;').replace(/'/g, '&#039;');
}
