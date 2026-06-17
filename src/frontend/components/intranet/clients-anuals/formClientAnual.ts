import { API_BASE } from '../../../config/globals';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

type UsuarioCreateData = {
  uuid: string;
  estado: string;
};

export const URLS = {
  GET: {
    USUARIOS_GET: (uuid: string) => `${API_BASE}/usuaris/get/?type=clienteAnual&uuid=${encodeURIComponent(uuid)}`,
  },
  POST: {
    USUARIOS_CREATE: `${API_BASE}/usuaris/post/?type=clienteAnual-create`,
  },
  PUT: {
    USUARIOS_UPDATE: `${API_BASE}/usuaris/put/?type=clienteAnual-update`,
  },
};


export interface ClienteAnualFitxa {
    [key: string]: unknown;
  uuid: string;
  nombre: string;
  email: string;
  estado?: string;

  empresa?: string | null;
  nif?: string | null;
  direccion?: string | null;
  ciudad?: string | null;
  codigo_postal?: string | null;
  pais?: string | null;
  telefono?: string | null;

  tipo_rol: string;
  locale: string;

  dispositiu?: string | null;
  navegador?: string | null;
  sistema_operatiu?: string | null;
  ip?: string | null;

  createdAt?: string | null;
  updatedAt?: string | null;
    fecha_inicio?: string | null;
    fecha_fin?: string | null;
    limite_reservas?: number | null;

    vehiculo?: string | null;
    matricula?: string | null;
    observaciones?: string | null;
  
}

function setHidden(id: string, value: string) {
  const el = document.getElementById(id) as HTMLInputElement | null;
  if (el) el.value = value;
}

function setTitle(html: string) {
  const div = document.getElementById('titolForm') as HTMLDivElement | null;
  if (div) div.innerHTML = html;
}

function isOk<T>(r: ApiResponse<T>): r is ApiOk<T> {
  return r.status === 'success';
}

export async function formClientAnual(isUpdate: boolean, uuid?: string) {
  const form = document.getElementById('formclientAnual') as HTMLFormElement | null;
  const btn = document.getElementById('btnAnual') as HTMLButtonElement | null;
  if (!form || !btn) return;

  // =========================
  // CREATE
  // =========================
  if (!isUpdate) {
    setTitle(`<h5>Clients/usuaris: alta nou client client</h5>`);
    btn.textContent = 'Inserir dades';

    form.addEventListener(
    'submit',
    (event) => {
      transmissioDadesDB(event, 'POST', 'formclientAnual', URLS.POST.USUARIOS_CREATE);
    },
    { once: true }
  );


    return;
  }

  // =========================
  // UPDATE
  // =========================
  if (!uuid) {
    setTitle(`<h5>Clients anuals: modificació dades</h5><p>Falta UUID per a carregar l'usuari.</p>`);
    btn.disabled = true;
    return;
  }

// Por esto:
const handleSubmit = (event: Event) => {
  transmissioDadesDB(event, 'PUT', 'formclientAnual', URLS.PUT.USUARIOS_UPDATE);
};
form.addEventListener('submit', handleSubmit);

  setTitle(`<h5>Clients anuals: modificació dades</h5>`);
  btn.textContent = 'Modificar dades';

  const res = await fetchDataGet<ApiResponse<ClienteAnualFitxa>>(URLS.GET.USUARIOS_GET(uuid));

if (!res || !isOk(res)) {
  setTitle(`<h2>Clients anuals: modificació</h2><p>No s'ha pogut carregar les dades de l'usuari.</p>`);
  btn.disabled = true;
  return;
}

const data = res.data;

// 👉 relleno campos usuario
renderFormInputs(data);

}
