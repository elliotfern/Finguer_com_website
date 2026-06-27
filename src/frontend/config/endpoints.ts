// config/endpoints.ts

import { apiUrl } from './globals';

export const ENDPOINTS = {
    GET: {
        reserves: {
            list: `${apiUrl}/intranet/reserves/get?type=list`,
            // ...
        },
        areaClient: {
            reservaClient: (email: string) =>
                `${apiUrl}/area-client/reservas?type=reservas&cliente=${encodeURIComponent(email)}`,
        },
        clients: {
            dadesClient: (uuid: string) =>
                `{apiUrl}/usuaris/get?type=get&uuid=${encodeURIComponent(uuid)}`,
        },
    },
    POST: {
        auth: {
            login: `${apiUrl}/intranet/auth/login`,
        },
        reserves: {
            updateEstado: `${apiUrl}/intranet/reserves/post?type=update-estado`,
            // ...
        },
        clients: {
            creacioClient: `${apiUrl}/usuaris/post?type=usuarios-create`,
        },
    },
    PUT: {
        clients: {
            updateClient: `${apiUrl}/usuaris/put?type=usuarios-update`,
        },
    },
    DELETE: {
        // ...
    },
};
