import { devDirectory } from '../../config.js';

export function deleteCookie(name: string, path?: string, domain?: string): void {
    if (getCookie(name)) {
        document.cookie = name + "=" +
            ((path) ? ";path=" + path : "") +
            ((domain) ? ";domain=" + domain : "") +
            ";expires=Thu, 01 Jan 1970 00:00:01 GMT";
    }
}

export function getCookie(name: string): string | undefined {
    const value = "; " + document.cookie;
    const parts = value.split("; " + name + "=");
    if (parts.length === 2) return parts.pop()?.split(";").shift();
    return undefined; // Asegúrate de manejar el caso donde no se encuentra la cookie
}

export function deleteAllCookies(): void {
    const cookies = document.cookie.split("; ");

    // Eliminar cookies en la ruta actual y todas las subrutas
    cookies.forEach(cookie => {
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        const pathParts = location.pathname.split('/');
        for (let i = 0; i < pathParts.length; i++) {
            const path = pathParts.slice(0, i + 1).join('/') || '/';
            deleteCookie(name, path);
            deleteCookie(name, path, window.location.hostname);
            if (window.location.hostname.includes('.')) {
                deleteCookie(name, path, '.' + window.location.hostname);
            }
        }
    });

    // Eliminar cookies en el dominio raíz
    cookies.forEach(cookie => {
        const eqPos = cookie.indexOf("=");
        const name = eqPos > -1 ? cookie.substr(0, eqPos) : cookie;
        deleteCookie(name, '/');
        deleteCookie(name, '/', window.location.hostname);
        if (window.location.hostname.includes('.')) {
            deleteCookie(name, '/', '.' + window.location.hostname);
        }
    });
}

export function logout(): void {
    // Eliminar todas las cookies
    deleteAllCookies();

    // Si usas localStorage o sessionStorage, también es buena idea limpiarlos
    localStorage.clear();
    sessionStorage.clear();

    // Redirigir a la página de inicio de sesión
    window.location.href = `${devDirectory}/login`;
}
