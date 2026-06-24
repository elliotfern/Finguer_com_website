import { API_BASE } from '../../../config/globals';
import { auxiliarSelect } from '../../../services/auxiliarSelect/auxiliarSelect';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

export const URLS = {
    GET: {
        USUARIOS_GET: (uuid: string) =>
            `${API_BASE}/clients/get/clientAnualReserva?uuid=${encodeURIComponent(uuid)}`,
    },
    POST: {
        USUARIOS_CREATE: `${API_BASE}/reserves/post/createReservaAnual`,
    },
    PUT: {
        USUARIOS_UPDATE: `${API_BASE}/reserves/put/updateReservaAnual`,
    },
};

export interface ClienteAnualFitxa {
    [key: string]: unknown;
    uuid_hex: string;
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
    const form = document.getElementById(
        'formReservaAnual'
    ) as HTMLFormElement | null;
    const btn = document.getElementById(
        'btnReservaAnual'
    ) as HTMLButtonElement | null;
    if (!form || !btn) return;

    // =========================
    // CREATE
    // =========================
    if (!isUpdate) {
        setTitle(`<h5>Client anual: alta Reserva</h5>`);
        btn.textContent = 'Inserir dades';

        await auxiliarSelect(
            '',
            '/api/clients/get/clientsAnuals',
            'usuario_uuid',
            'nom'
        );

        const handleSubmit = (event: Event) => {
            transmissioDadesDB(
                event,
                'POST',
                'formReservaAnual',
                URLS.POST.USUARIOS_CREATE
            );
        };
        form.addEventListener('submit', handleSubmit);
    } else {
        // =========================
        // UPDATE
        // =========================
        if (!uuid) {
            setTitle(
                `<h5>Clients anuals: modificació Reserva</h5><p>Falta UUID per a carregar l'usuari.</p>`
            );
            btn.disabled = true;
            return;
        }

        // Por esto:
        const handleSubmit = (event: Event) => {
            transmissioDadesDB(
                event,
                'POST',
                'formReservaAnual',
                URLS.POST.USUARIOS_CREATE
            );
        };
        form.addEventListener('submit', handleSubmit);

        setTitle(`<h5>Clients anuals: alta nova Reserva</h5>`);
        btn.textContent = 'Introduir dades';

        const res = await fetchDataGet<ApiResponse<ClienteAnualFitxa>>(
            URLS.GET.USUARIOS_GET(uuid)
        );

        if (!res || !isOk(res)) {
            setTitle(
                `<h2>Clients anuals: alta Reserva</h2><p>No s'ha pogut carregar les dades de l'usuari.</p>`
            );
            btn.disabled = true;
            return;
        }

        const data = res.data;

        // 👉 relleno campos usuario
        renderFormInputs(data);

        const usuari = document.getElementById(
            'usuario_uuid_hidden'
        ) as HTMLInputElement | null;

        if (usuari) {
            usuari.value = data.uuid_hex;
        }

        await auxiliarSelect(
            data.uuid_hex,
            '/api/clients/get/clientsAnuals',
            'usuario_uuid',
            'nom',
            undefined,
            undefined,
            true
        );
    }
}
