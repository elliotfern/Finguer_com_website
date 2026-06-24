import { isAdmin } from '../auth/store';

interface ClientAnual {
    nom: string;
    telefon: string;
    uuid_hex: string;
    fecha_fin: string | null;
    estado: string;
    reservas_completadas: number;
    limite_reservas: number;
    is_admin: boolean;
    app_web: string;
}

interface ApiResponse {
    data: ClientAnual[];
}

// ----------------------
// HELPERS SAFE
// ----------------------

function safeText(value: unknown, fallback = '–'): string {
    if (value === null || value === undefined || value === '') {
        return fallback;
    }
    return String(value);
}

function safeNumber(value: unknown, fallback = 0): number {
    const n = Number(value);
    return Number.isFinite(n) ? n : fallback;
}

function escaparHtml(str: string): string {
    return str
        .replace(/&/g, '&amp;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

// ----------------------
// FECHAS
// ----------------------

function formatDateES(dateStr: string): string {
    const date = new Date(dateStr);
    const day = String(date.getUTCDate()).padStart(2, '0');
    const month = String(date.getUTCMonth() + 1).padStart(2, '0');
    const year = date.getUTCFullYear();
    return `${day}-${month}-${year}`;
}

function diasRestantes(fechaFin: string): number {
    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0);

    const fin = new Date(fechaFin);
    fin.setHours(0, 0, 0, 0);

    return Math.floor((fin.getTime() - hoy.getTime()) / (1000 * 60 * 60 * 24));
}

// ----------------------
// CELDAS
// ----------------------

function celdaFechaFin(fechaFin: string | null): {
    html: string;
    caducaPronto: boolean;
} {
    if (!fechaFin) {
        return { html: '–', caducaPronto: false };
    }

    const dias = diasRestantes(fechaFin);
    const fechaFormateada = formatDateES(fechaFin);

    if (dias <= 30) {
        return {
            html: `<span class="badge bg-danger">⚠ Caduca ${fechaFormateada}</span>`,
            caducaPronto: true,
        };
    }

    return { html: fechaFormateada, caducaPronto: false };
}

function celdaAccionesAdmin(
    id: string | null | undefined,
    baseUrl: string
): string {
    const safeId = id ?? '';

    if (!isAdmin() || !safeId) {
        return `
            <td class="text-muted text-center">–</td>
            <td class="text-muted text-center">–</td>
        `;
    }

    return `
        <td>
            <a href="${baseUrl}/modifica-client/${safeId}" class="btn btn-warning btn-sm">
                Actualitzar dades
            </a>
        </td>
        <td>
            <a href="${baseUrl}/eliminar-client/${safeId}" class="btn btn-danger btn-sm">
                Eliminar client
            </a>
        </td>
    `;
}

// ----------------------
// FILAS
// ----------------------

function construirFila(client: ClientAnual): HTMLTableRowElement {
    const { html: fechaHtml, caducaPronto } = celdaFechaFin(client.fecha_fin);

    const tr = document.createElement('tr');

    if (caducaPronto) {
        tr.classList.add('table-danger');
    }

    const baseUrl = `/control/clients-anuals`;

    tr.innerHTML = `
        <td>${escaparHtml(safeText(client.nom))}</td>
        <td>${escaparHtml(safeText(client.telefon))}</td>
        <td>${fechaHtml}</td>
        <td>
            <strong>
                ${safeNumber(client.reservas_completadas)} de ${safeNumber(client.limite_reservas)}
            </strong>
        </td>
        <td>
            <span class="badge bg-secondary">
                ${escaparHtml(safeText(client.estado))}
            </span>
        </td>
        ${celdaAccionesAdmin(client.uuid_hex, baseUrl)}
        <td>
            <a href="${baseUrl}/modifica-reserva/${client.uuid_hex ?? ''}" class="btn btn-info btn-sm">
                Crear reserva
            </a>
        </td>
    `;

    return tr;
}

// ----------------------
// FETCH + RENDER
// ----------------------

export async function taulaClientsAnuals(): Promise<void> {
    const tbody = document.querySelector<HTMLTableSectionElement>(
        '#taula-clients-anuals tbody'
    );

    if (!tbody) {
        console.error(
            "No s'ha trobat el tbody de la taula #taula-clients-anuals"
        );
        return;
    }

    try {
        const response = await fetch('/api/clients/get/clientsAnuals');

        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }

        const json: ApiResponse = await response.json();
        const clients = json.data;

        tbody.innerHTML = '';

        if (!clients || clients.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="text-center text-muted">
                        No hi ha clients amb abonament anual.
                    </td>
                </tr>
            `;
            return;
        }

        for (const client of clients) {
            tbody.appendChild(construirFila(client));
        }
    } catch (error: unknown) {
        const msg = error instanceof Error ? error.message : 'Error desconegut';

        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="text-danger text-center">
                    Error carregant les dades: ${escaparHtml(msg)}
                </td>
            </tr>
        `;
    }
}
