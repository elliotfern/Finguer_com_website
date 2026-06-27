import { ENDPOINTS } from '../../../config/endpoints';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

type UsuarioCreateData = {
    uuid: string;
    estado: string;
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
    const form = document.getElementById(
        'UsuariosForm'
    ) as HTMLFormElement | null;
    const btn = document.getElementById(
        'btnUsuarios'
    ) as HTMLButtonElement | null;
    if (!form || !btn) return;

    // =========================
    // CREATE
    // =========================
    if (!isUpdate) {
        setTitle(`<h5>Clients/usuaris: alta nou client</h5>`);
        btn.textContent = 'Inserir dades';

        setHidden('uuid', '');
        setHidden('estado', 'activo');

        form.addEventListener(
            'submit',
            (event) => {
                transmissioDadesDB<UsuarioCreateData>(
                    event,
                    'POST',
                    'UsuariosForm',
                    ENDPOINTS.POST.clients.creacioClient,
                    false,
                    'hide'
                );
            },
            { once: true }
        );

        return;
    }

    // =========================
    // UPDATE
    // =========================
    if (!uuid) {
        setTitle(
            `<h5>Clients/usuaris: modificació dades</h5><p>Falta UUID per a carregar l'usuari.</p>`
        );
        btn.disabled = true;
        return;
    }

    setTitle(`<h5>Clients/usuaris: modificació dades</h5>`);
    btn.textContent = 'Modificar dades';

    const res = await fetchDataGet<ApiResponse<UsuarioFitxa>>(
        ENDPOINTS.GET.clients.dadesClient(uuid)
    );
    if (!res || !isOk(res)) {
        setTitle(
            `<h2>Clients/usuaris: modificació</h2><p>No s'ha pogut carregar les dades de l'usuari.</p>`
        );
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
            transmissioDadesDB(
                event,
                'PUT',
                'UsuariosForm',
                ENDPOINTS.PUT.clients.updateClient
            );
        },
        { once: true }
    );
}
