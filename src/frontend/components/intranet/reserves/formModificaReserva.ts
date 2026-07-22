import { ENDPOINTS } from '../../../config/endpoints';
import { API_URL } from '../../../config/environment';
import { auxiliarSelect } from '../../../services/auxiliarSelect/auxiliarSelect';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

export const URLS = {
    GET: {
        USUARIOS_GET: (id: number) =>
            `${API_URL}/intranet/reserves/get?type=reservaId&id=${encodeURIComponent(id)}`,
    },
    POST: {
        USUARIOS_CREATE: `${API_URL}/reserves/post/updateReserva`,
    },
    PUT: {
        USUARIOS_UPDATE: `${API_URL}/reserves/put/updateReserva`,
    },
};

export interface ClienteAnualFitxa {
    [key: string]: unknown;
    usuario_uuid: string;
    id: string;
    vehiculo?: string | null;
    matricula?: string | null;
    observaciones?: string | null;
    canal?: string;
    estado?: string;
    estado_vehiculo?: string;
    tipo?: string;
}

function setTitle(html: string) {
    const div = document.getElementById('titolForm') as HTMLDivElement | null;
    if (div) div.innerHTML = html;
}

function isOk<T>(r: ApiResponse<T>): r is ApiOk<T> {
    return r.status === 'success';
}

export async function formReservaClient(isUpdate: boolean, id?: number) {
    console.log('formReservaClient', isUpdate, id);
    const form = document.getElementById(
        'reservaForm'
    ) as HTMLFormElement | null;
    const btn = document.getElementById(
        'btnReserva'
    ) as HTMLButtonElement | null;
    if (!form || !btn) return;

    // =========================
    // CREATE
    // =========================
    if (!isUpdate) {
        setTitle(`<h5>Alta nova Reserva</h5>`);
        btn.textContent = 'Inserir dades';

        await auxiliarSelect(
            '',
            '/api/intranet/reserves/get?type=tipoReserva',
            'tipo',
            'tipo',
            undefined,
            undefined
        );

        await auxiliarSelect(
            '',
            '/api/intranet/reserves/get?type=canalReserva',
            'canal',
            'canal',
            undefined,
            undefined
        );

        await auxiliarSelect(
            '',
            '/api/intranet/reserves/get?type=estado',
            'estado',
            'label',
            undefined,
            undefined
        );

        await auxiliarSelect(
            '',
            '/api/intranet/reserves/get?type=estado_vehiculo',
            'estado_vehiculo',
            'label',
            undefined,
            undefined
        );

        const handleSubmit = (event: Event) => {
            transmissioDadesDB(
                event,
                'POST',
                'reservaForm',
                URLS.POST.USUARIOS_CREATE
            );
        };
        form.addEventListener('submit', handleSubmit);
    } else {
        // =========================
        // UPDATE
        // =========================
        if (!id) {
            setTitle(`<h5>Modificació de Reserva - error</h5>`);
            btn.disabled = true;
            return;
        }

        // Por esto:
        const handleSubmit = (event: Event) => {
            transmissioDadesDB(
                event,
                'PUT',
                'reservaForm',
                ENDPOINTS.PUT.reserves.actualizarReserva
            );
        };
        form.addEventListener('submit', handleSubmit);

        setTitle(`<h5>Modificació reserva</h5>`);
        btn.textContent = 'Modificar dades';

        const res = await fetchDataGet<ApiResponse<ClienteAnualFitxa>>(
            URLS.GET.USUARIOS_GET(id)
        );

        if (!res || !isOk(res)) {
            setTitle(`<h5>Modificació reserva - Error dades</h5>`);
            btn.disabled = true;
            return;
        }

        const data = res.data;

        // 👉 relleno campos usuario
        renderFormInputs(data);

        const usuari = document.getElementById(
            'usuario_uuid'
        ) as HTMLInputElement | null;

        const reserva = document.getElementById(
            'id'
        ) as HTMLInputElement | null;

        if (usuari) {
            usuari.value = data.usuario_uuid;
        }

        if (reserva) {
            reserva.value = data.id;
        }

        await auxiliarSelect(
            data.tipo,
            '/api/intranet/reserves/get?type=tipoReserva',
            'tipo',
            'tipo',
            undefined,
            undefined
        );

        await auxiliarSelect(
            data.canal,
            '/api/intranet/reserves/get?type=canalReserva',
            'canal',
            'canal',
            undefined,
            undefined
        );

        await auxiliarSelect(
            data.estado,
            '/api/intranet/reserves/get?type=estado',
            'estado',
            'label',
            undefined,
            undefined
        );

        await auxiliarSelect(
            data.estado_vehiculo,
            '/api/intranet/reserves/get?type=estado_vehiculo',
            'estado_vehiculo',
            'label',
            undefined,
            undefined
        );
    }
}
