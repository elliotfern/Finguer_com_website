// config/endpoints.ts

import { API_URL } from './environment';

export const ENDPOINTS = {
    GET: {
        reserves: {
            list: `${API_URL}/intranet/reserves/get?type=list`,
            // ...
        },
        areaClient: {
            reservaClient: (email: string) =>
                `${API_URL}/area-client/reservas?type=reservas&cliente=${encodeURIComponent(email)}`,
        },
        clients: {
            dadesClient: (uuid: string) =>
                `${API_URL}/usuaris/get?type=usuarios-get&uuid=${encodeURIComponent(uuid)}`,
            nomUsuari: `${API_URL}/intranet/users/get?type=user`,
        },
    },
    POST: {
        auth: {
            login: `${API_URL}/intranet/auth/login`,
        },
        reserves: {
            updateEstado: `${API_URL}/intranet/reserves/post?type=update-estado`,
            // ...
        },
        clients: {
            creacioClient: `${API_URL}/usuaris/post?type=usuarios-create`,
        },
    },
    PUT: {
        clients: {
            updateClient: `${API_URL}/usuaris/put?type=usuarios-update`,
        },
    },
    DELETE: {
        // ...
    },
};
