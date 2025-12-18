// globals.ts

declare global {
  interface Window {
    //APP_API_BASE?: string;
    //APP_WEB_BASE?: string;
    APP_REDSYS_URL?: string;
  }
}

// 1) Base de API: por defecto relativa (funciona en dev y prod)
export const API_BASE = '/api';
export const WEB_BASE = window.location.origin;

// 2) Helper para construir endpoints sin errores de barras
export const apiUrl = API_BASE;

export const webUrl = WEB_BASE;

export const redsysUrl = window.APP_REDSYS_URL || '';
if (!redsysUrl) {
  throw new Error('Missing APP_REDSYS_URL');
}
