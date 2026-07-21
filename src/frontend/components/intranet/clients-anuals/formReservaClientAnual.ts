import { ENDPOINTS } from '../../../config/endpoints';
import { API_URL } from '../../../config/environment';
import { auxiliarSelect } from '../../../services/auxiliarSelect/auxiliarSelect';
import { ApiOk, ApiResponse } from '../../../types/api';
import { fetchDataGet } from '../../../utils/fetchDataGet';
import { renderFormInputs } from '../../../utils/renderFormInputs';
import { transmissioDadesDB } from '../../../utils/transmissioDadesBD';

export const URLS = {
    GET: {
        USUARIOS_GET: (uuid: string) =>
            `${API_URL}/clients/get/clientAnualReserva?uuid=${encodeURIComponent(uuid)}`,
        RESERVA_GET: (id: string) =>
            `${API_URL}/intranet/reserves/get?type=reservaId&id=${encodeURIComponent(id)}`,
    },
};

export interface ClienteAnualFitxa {
    [key: string]: unknown;
    uuid_hex: string;
    vehiculo?: string | null;
    matricula?: string | null;
    observaciones?: string | null;
}

// Shape real de la respuesta de type=reservaId
interface ReservaAnualData {
    usuario_uuid?: string;
    localizador?: string;
    entrada_prevista?: string; // "2026-04-09 17:30:00"
    salida_prevista?: string;
    vuelo?: string;
    vehiculo?: string;
    matricula?: string;
    notas?: string;
    [key: string]: unknown;
}

// Shape que espera el formulario HTML (por name de los inputs)
interface ReservaAnualFormData {
    [key: string]: unknown;
    usuario_uuid: string;
    localizador: string;
    diaEntrada?: string;
    horaEntrada?: string;
    diaSalida?: string;
    horaSalida?: string;
    vuelo?: string;
    vehiculo?: string;
    matricula?: string;
    notes?: string;
}

function setTitle(html: string) {
    const div = document.getElementById('titolForm') as HTMLDivElement | null;
    if (div) div.innerHTML = html;
}

function isOk<T>(r: ApiResponse<T>): r is ApiOk<T> {
    return r.status === 'success';
}

// Convierte un UUID en formato hex compacto (32 chars, sin guiones) a formato estándar 8-4-4-4-12.
function hexToUuid(hex: string): string {
    const clean = hex.replace(/-/g, '').toLowerCase();
    if (clean.length !== 32) return hex;
    return `${clean.slice(0, 8)}-${clean.slice(8, 12)}-${clean.slice(12, 16)}-${clean.slice(16, 20)}-${clean.slice(20)}`;
}

// Parte un datetime "YYYY-MM-DD HH:MM:SS" en { fecha: "YYYY-MM-DD", hora: "HH:MM" }
function splitDateTime(value?: string): { fecha: string; hora: string } {
    if (!value) return { fecha: '', hora: '' };
    const [fecha, horaCompleta] = value.split(' ');
    const hora = horaCompleta ? horaCompleta.slice(0, 5) : '';
    return { fecha: fecha ?? '', hora };
}

// Adapta la respuesta de type=reservaId al shape que espera el HTML del formulario (solo UPDATE)
function mapReservaToFormData(data: ReservaAnualData): ReservaAnualFormData {
    const entrada = splitDateTime(data.entrada_prevista);
    const salida = splitDateTime(data.salida_prevista);

    return {
        usuario_uuid: data.usuario_uuid ? hexToUuid(data.usuario_uuid) : '',
        localizador: data.localizador ?? '',
        diaEntrada: entrada.fecha,
        horaEntrada: entrada.hora,
        diaSalida: salida.fecha,
        horaSalida: salida.hora,
        vuelo: data.vuelo ?? '',
        vehiculo: data.vehiculo ?? '',
        matricula: data.matricula ?? '',
        notes: data.notas ?? '',
    };
}

async function obtenirReserva(
    idReserva: string
): Promise<ReservaAnualData | null> {
    const res = await fetchDataGet<ApiResponse<ReservaAnualData>>(
        URLS.GET.RESERVA_GET(idReserva)
    );

    if (!res || !isOk(res)) return null;

    return res.data;
}

/**
 * @param isUpdate  true = modificació d'una reserva existent (segon paràmetre = idReserva)
 *                  false = alta d'una reserva nova (segon paràmetre opcional = uuid del client preseleccionat)
 */
export async function formReservaClientAnual(
    isUpdate: boolean,
    idReservaOrUuid?: string
) {
    const form = document.getElementById(
        'formReservaAnual'
    ) as HTMLFormElement | null;
    const btn = document.getElementById(
        'btnReservaAnual'
    ) as HTMLButtonElement | null;
    if (!form || !btn) return;

    // 1) Configurar título, botón y submit según CREATE vs UPDATE
    if (!isUpdate) {
        setTitle(`<h5>Client anual: alta Reserva</h5>`);
        btn.textContent = 'Inserir dades';

        form.addEventListener('submit', (event: Event) => {
            transmissioDadesDB(
                event,
                'POST',
                'formReservaAnual',
                ENDPOINTS.POST.reserves.crearReservaAnual
            );
        });
    } else {
        if (!idReservaOrUuid) {
            setTitle(
                `<h5>Clients anuals: modificació Reserva</h5><p>Falta l'idReserva per a carregar les dades.</p>`
            );
            btn.disabled = true;
            return;
        }

        setTitle(`<h5>Clients anuals: modificació Reserva</h5>`);
        btn.textContent = 'Actualitzar dades';

        form.addEventListener('submit', (event: Event) => {
            transmissioDadesDB(
                event,
                'PUT',
                'formReservaAnual',
                ENDPOINTS.PUT.reserves.actualizarReservaAnual
            );
        });
    }

    // 2) Cargar / preseleccionar cliente
    if (isUpdate && idReservaOrUuid) {
        // UPDATE: idReservaOrUuid es el idReserva -> endpoint reservaId
        const reserva = await obtenirReserva(idReservaOrUuid);

        if (!reserva) {
            setTitle(
                `<h5>Clients anuals: modificació Reserva</h5><p>No s'ha pogut carregar les dades de la reserva.</p>`
            );
            btn.disabled = true;
            return;
        }

        const formData = mapReservaToFormData(reserva);
        renderFormInputs(formData);

        const usuari = document.getElementById(
            'usuario_uuid_hidden'
        ) as HTMLInputElement | null;
        if (usuari) usuari.value = formData.usuario_uuid;

        const localizador = document.getElementById(
            'localizador'
        ) as HTMLInputElement | null;
        if (localizador) localizador.value = formData.localizador;

        await auxiliarSelect(
            formData.usuario_uuid,
            '/api/clients/get/clientsAnuals',
            'usuario_uuid',
            'nom',
            undefined,
            undefined,
            true
        );
    } else if (!isUpdate && idReservaOrUuid) {
        // CREATE con cliente preseleccionado: idReservaOrUuid es el uuid del cliente -> endpoint original
        const res = await fetchDataGet<ApiResponse<ClienteAnualFitxa>>(
            URLS.GET.USUARIOS_GET(idReservaOrUuid)
        );

        if (!res || !isOk(res)) {
            setTitle(
                `<h2>Clients anuals: alta Reserva</h2><p>No s'ha pogut carregar les dades de l'usuari.</p>`
            );
            btn.disabled = true;
            return;
        }

        const data = res.data;
        renderFormInputs(data);

        const usuari = document.getElementById(
            'usuario_uuid_hidden'
        ) as HTMLInputElement | null;
        if (usuari) usuari.value = data.uuid_hex;

        await auxiliarSelect(
            data.uuid_hex,
            '/api/clients/get/clientsAnuals',
            'usuario_uuid',
            'nom',
            undefined,
            undefined,
            true
        );
    } else {
        // CREATE sin cliente preseleccionado: select vacío, el usuario elige el cliente manualmente
        await auxiliarSelect(
            '',
            '/api/clients/get/clientsAnuals',
            'usuario_uuid',
            'nom'
        );
    }
}
