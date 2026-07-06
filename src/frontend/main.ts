// main.ts
import { detectAndRedirect } from './utils/selectorIdioma';

import { formUsuarios } from './components/intranet/clients/formClient';
import { clientsUsersTable } from './components/intranet/clients/llistatClients';
import { reservesClientPage } from './components/intranet/clients/reservesClient';
import { initTaulaFacturacio } from './components/intranet/facturacio/llistatFactures';
import { reserves } from './components/intranet/reserves/reservesPendents';

import { areaClientHistoricReserves } from './components/areaClient/areaClientHistoricReserves';
import { areaClientLogin } from './components/areaClient/areaClientLogin';
import { finguerAnualContactForm } from './components/homepage/formulari';
import { homePage } from './components/homepage/homepage';
import { applyRoleToDom } from './components/intranet/auth/applyRole';
import { setMe } from './components/intranet/auth/store';
import { formClientAnual } from './components/intranet/clients-anuals/formClientAnual';
import { formReservaClientAnual } from './components/intranet/clients-anuals/formReservaClientAnual';
import { taulaClientsAnuals } from './components/intranet/clients-anuals/taulaClientsAnuals';
import { header } from './components/intranet/header/header';
import { nomUsuari } from './components/intranet/header/nomUsuari';
import { login } from './components/intranet/login/login';
import { formReservaClient } from './components/intranet/reserves/formModificaReserva';
import { pagament } from './components/pagament/pagament';

const supportedLanguages = ['es', 'fr', 'en', 'ca'] as const;

function normalizePath(): string {
    return window.location.pathname.replace(/\/+$/, '') || '/';
}

// Ruta normalizada: sin barra final (excepto "/")
const path = normalizePath();

// Normalizamos para la web multidioma (usa mismo path normalizado)
const isReservaPage =
    path === '/reserva' ||
    path === '/' ||
    supportedLanguages.some(
        (lang) => path === `/${lang}` || path.startsWith(`/${lang}/reserva`)
    );

const isPagoPage =
    path === '/pago' ||
    supportedLanguages.some((lang) => path.startsWith(`/${lang}/pago`)) ||
    /^\/pago\/[a-zA-Z0-9]+$/.test(path);

function getUuidFromPath(prefix: string): string | undefined {
    // prefix ejemplo: '/control/usuaris/modifica-client'
    const p = window.location.pathname.replace(/\/+$/, ''); // quita trailing /
    if (!p.startsWith(prefix + '/')) return undefined;

    const uuid = p.slice(prefix.length + 1); // lo que viene después del /
    return uuid ? decodeURIComponent(uuid) : undefined;
}

// --------------------
// Web Finguer.com
// --------------------
if (isReservaPage) {
    homePage();
    finguerAnualContactForm();
}

if (isPagoPage) {
    pagament();
}

// --------------------
// Área cliente
// --------------------
if (path === '/area-cliente/login') {
    detectAndRedirect();
    areaClientLogin();
}

if (path === '/area-cliente/reservas') {
    detectAndRedirect();
    areaClientHistoricReserves();
}

// --------------------
// Intranet
// --------------------
const isIntranet = path === '/control' || path.startsWith('/control/');

async function initIntranet(): Promise<void> {
    // 1) cargar usuario + guardar en store
    const me = await nomUsuari();
    setMe(me);

    header();

    // 3) aplicar permisos visuales sobre HTML existente (PHP)
    if (me) applyRoleToDom(me.role);

    // 4) cargar la página concreta de intranet
    routeIntranet(path);
}

function routeIntranet(p: string): void {
    // -------------------------
    // CLIENTS ANUALS (PRIMERO)
    // -------------------------
    if (p.startsWith('/control/clients-anuals/nou-client')) {
        formClientAnual(false);
        return;
    }

    if (p.startsWith('/control/clients-anuals/modifica-client')) {
        const uuid = getUuidFromPath('/control/clients-anuals/modifica-client');
        formClientAnual(true, uuid);
        return;
    }

    if (p.startsWith('/control/clients-anuals/pendents')) {
        reserves('pendiente_entrada', '3');
        return;
    }

    if (p.startsWith('/control/clients-anuals/parking')) {
        reserves('dentro', '3');
        return;
    }

    if (p.startsWith('/control/clients-anuals/completades')) {
        reserves('salido', '3');
        return;
    }

    if (p.startsWith('/control/clients-anuals/nova-reserva')) {
        formReservaClientAnual(false);
        return;
    }

    if (p.startsWith('/control/clients-anuals/modifica-reserva')) {
        const uuid = getUuidFromPath(
            '/control/clients-anuals/modifica-reserva'
        );
        formReservaClientAnual(true, uuid);
        return;
    }

    if (p.startsWith('/control/clients-anuals')) {
        taulaClientsAnuals();
        return;
    }

    // -------------------------
    // USUARIS
    // -------------------------
    if (p.startsWith('/control/usuaris/alta-client')) {
        formUsuarios(false);
        return;
    }

    if (p.startsWith('/control/usuaris/modifica-client/')) {
        const uuid = getUuidFromPath('/control/usuaris/modifica-client');
        formUsuarios(true, uuid);
        return;
    }

    if (p.startsWith('/control/usuaris/reserves-client')) {
        reservesClientPage();
        return;
    }

    if (p.startsWith('/control/usuaris')) {
        clientsUsersTable();
        return;
    }

    // -------------------------
    // FACTURACIÓ
    // -------------------------
    if (p.startsWith('/control/facturacio')) {
        initTaulaFacturacio();
        return;
    }

    // -------------------------
    // RESERVES ESPECÍFICAS
    // -------------------------
    if (p === '/control/reserves-parking') {
        reserves('dentro');
        return;
    }

    if (p === '/control/reserves-completades') {
        reserves('salido');
        return;
    }

    if (p === '/control' || p === '/control/reserves-pendents') {
        reserves('pendiente_entrada');
        return;
    }

    if (p.startsWith('/control/modifica-reserva/')) {
        const id = getUuidFromPath('/control/modifica-reserva');
        if (id) {
            formReservaClient(true, Number(id));
        }
        return;
    }
}

// Arranque intranet (excepto login)
if (isIntranet && path !== '/control/login') {
    initIntranet();
}

// LOGIN intranet (separado a propósito)
if (path === '/control/login') {
    login();
}

// fi
