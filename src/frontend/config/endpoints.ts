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
        catalogo: {
            configuracionReserva: `${API_URL}/catalogo/get/configuracion-reserva`,
            horasDisponibles: `${API_URL}/catalogo/get/horas-disponibles`,
        },
        carrito: {
            obtenerCarrito: `${API_URL}/carrito/get`,
        },
    },
    POST: {
        auth: {
            login: `${API_URL}/usuaris/login`,
        },
        reserves: {
            updateEstado: `${API_URL}/intranet/reserves/post?type=update-estado`,
            CrearReserva: `${API_URL}/reserva/post/alta-reserva`,

            // ...
        },
        clients: {
            creacioClient: `${API_URL}/usuaris/post?type=usuarios-create`,
            altaClient: `${API_URL}/usuaris/alta-client?type=usuarios-create`,
        },
        carrito: {
            guardarCarrito: `${API_URL}/carrito/post`,
        },
        pago: {
            pagamentRedsysTargeta: `${API_URL}/pago/post/pagament-redsys-targeta`,
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
