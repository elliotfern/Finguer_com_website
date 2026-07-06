import { API_URL } from '../../../config/environment';

// logout.ts
export const logout = async (event: Event) => {
    event.preventDefault();

    try {
        const response = await fetch(
            `${API_URL}/intranet/users/get?type=logout`,
            {
                method: 'GET',
                credentials: 'include',
            }
        );

        // aunque el backend devuelva 204/200, redirigimos igual
        if (!response.ok && response.status !== 204) {
            // opcional: leer body si hay json
            console.warn('Logout response not ok:', response.status);
        }
    } catch (err) {
        console.error('Logout error:', err);
    } finally {
        window.location.href = '/control/login';
    }
};
