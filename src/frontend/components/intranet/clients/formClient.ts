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
    USUARIOS_GET: (uuid: string) => `${API_BASE}/usuaris/get/?type=get&uuid=${encodeURIComponent(uuid)}`,
  },
  POST: {
    USUARIOS_CREATE: `${API_BASE}/usuaris/post/?type=usuarios-create`,
  },
  PUT: {
    USUARIOS_UPDATE: `${API_BASE}/usuaris/put/?type=usuarios-update`,
  },
};


export interface UsuarioFitxa {
  [key: string]: unknown;
  uuid: string;
  nombre: string;
  email: string;
  estado?: string;

  password?: string | null; // NO vendrá del GET, pero lo dejamos por compatibilidad
  empresa?: string | null;
  nif?: string | null;
  direccion?: string | null;
  ciudad?: string | null;
  codigo_postal?: string | null;
  pais?: string | null;
  telefono?: string | null;
  anualitat?: string | null;

  tipo_rol: string;
  locale: string;

  dispositiu?: string | null;
  navegador?: string | null;
  sistema_operatiu?: string | null;
  ip?: string | null;

  createdAt?: string | null;
  updatedAt?: string | null;
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

export async function formUsuarios(isUpdate: boolean, uuid?: string) {
  const form = document.getElementById('UsuariosForm') as HTMLFormElement | null;
  const btn = document.getElementById('btnUsuarios') as HTMLButtonElement | null;
  if (!form || !btn) return;

  // =========================
  // CREATE
  // =========================
  if (!isUpdate) {
    setTitle(`<h2>Usuarios: creación de nuevo usuario/cliente</h2>`);
    btn.textContent = 'Insertar datos';

    setHidden('uuid', '');
    setHidden('estado', 'activo');

    form.addEventListener(
      'submit',
      (event) => {
        transmissioDadesDB<UsuarioCreateData>(event, 'POST', 'UsuariosForm', URLS.POST.USUARIOS_CREATE, false, 'hide');
      },
      { once: true }
    );

    return;
  }

  // =========================
  // UPDATE
  // =========================
  if (!uuid) {
    setTitle(`<h2>Usuarios: modificación</h2><p>Falta UUID para cargar el usuario.</p>`);
    btn.disabled = true;
    return;
  }

  setTitle(`<h2>Usuarios: modificación de usuario/cliente</h2>`);
  btn.textContent = 'Modificar datos';

  const res = await fetchDataGet<ApiResponse<UsuarioFitxa>>(URLS.GET.USUARIOS_GET(uuid));
  if (!res || !isOk(res)) {
    setTitle(`<h2>Usuarios: modificación</h2><p>No se han podido cargar los datos del usuario.</p>`);
    btn.disabled = true;
    return;
  }

  const data = res.data;

  renderFormInputs(data);
  setHidden('uuid', data.uuid ?? uuid);
  setHidden('estado', 'activo');

  const pass = document.getElementById('password') as HTMLInputElement | null;
  if (pass) pass.value = '';

  form.addEventListener(
    'submit',
    (event) => {
      transmissioDadesDB(event, 'PUT', 'UsuariosForm', URLS.PUT.USUARIOS_UPDATE);
    },
    { once: true }
  );
}
