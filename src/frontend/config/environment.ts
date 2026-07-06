declare global {
    interface Window {
        APP_ENV?: 'local' | 'staging' | 'production';
        APP_API_BASE?: string;
        APP_DOMAIN_WEB?: string;
        APP_REDSYS_URL?: string;
    }
}

function requireEnvVar(name: string, value: string | undefined): string {
    if (!value) {
        throw new Error(
            `Missing ${name}: revisa que el PHP inyecte esta variable en window`
        );
    }
    return value;
}

export const ENTORNO = window.APP_ENV ?? 'production';

// API_BASE en .env YA incluye el sufijo /api — no concatenar de nuevo
export const API_URL = requireEnvVar('APP_API_BASE', window.APP_API_BASE);

export const WEB_BASE = requireEnvVar('APP_DOMAIN_WEB', window.APP_DOMAIN_WEB);

export const redsysUrl = requireEnvVar('APP_REDSYS_URL', window.APP_REDSYS_URL);
