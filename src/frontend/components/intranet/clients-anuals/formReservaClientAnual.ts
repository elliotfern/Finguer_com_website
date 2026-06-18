import { API_BASE } from '../../../config/globals';
import { auxiliarSelect } from '../../../services/auxiliarSelect/auxiliarSelect';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

export const URLS = {
  GET: {
    USUARIOS_GET: (uuid: string) => `${API_BASE}/clients/get/clientAnualReserva?uuid=${encodeURIComponent(uuid)}`,
  },
  POST: {
    USUARIOS_CREATE: `${API_BASE}/clients/post/?type=clienteAnual-create`,
  },
  PUT: {
    USUARIOS_UPDATE: `${API_BASE}/clients/put/?type=clienteAnual-update`,
  },
};

export interface ClienteAnualFitxa {
  [key: string]: unknown;
  uuid_hex: string;
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

function setTitle(html: string) {
  const div = document.getElementById('titolForm') as HTMLDivElement | null;
  if (div) div.innerHTML = html;
}

function isOk<T>(r: ApiResponse<T>): r is ApiOk<T> {
  return r.status === 'success';
}

export async function formReservaClientAnual(isUpdate: boolean, uuid?: string) {
  const form = document.getElementById('formReservaAnual') as HTMLFormElement | null;
  const btn = document.getElementById('btnReservaAnual') as HTMLButtonElement | null;
  if (!form || !btn) return;

  // =========================
  // CREATE
  // =========================
  if (!isUpdate) {
    setTitle(`<h5>Client anual: alta Reserva</h5>`);
    btn.textContent = 'Inserir dades';

    const handleSubmit = (event: Event) => {
      transmissioDadesDB(event, 'POST', 'formReservaAnual', URLS.POST.USUARIOS_CREATE);
    };
    form.addEventListener('submit', handleSubmit);
  } else {
    // =========================
    // UPDATE
    // =========================
    if (!uuid) {
      setTitle(`<h5>Clients anuals: modificació Reserva</h5><p>Falta UUID per a carregar l'usuari.</p>`);
      btn.disabled = true;
      return;
    }

    // Por esto:
    const handleSubmit = (event: Event) => {
      transmissioDadesDB(event, 'PUT', 'formReservaAnual', URLS.PUT.USUARIOS_UPDATE);
    };
    form.addEventListener('submit', handleSubmit);

    setTitle(`<h5>Clients anuals: modificació Reserva</h5>`);
    btn.textContent = 'Modificar dades';

    const res = await fetchDataGet<ApiResponse<ClienteAnualFitxa>>(URLS.GET.USUARIOS_GET(uuid));

    if (!res || !isOk(res)) {
      setTitle(`<h2>Clients anuals: modificació Reserva</h2><p>No s'ha pogut carregar les dades de l'usuari.</p>`);
      btn.disabled = true;
      return;
    }

    const data = res.data;

    // 👉 relleno campos usuario
    renderFormInputs(data);

      await auxiliarSelect(data.uuid_hex, '/api/clients/get/clientsAnuals', 'usuario_uuid', 'nom');

  }

}
