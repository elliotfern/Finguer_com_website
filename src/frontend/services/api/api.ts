// api.ts

// Función para hacer las llamadas a la API con un tipo genérico 'T' para la respuesta y 'B' para el cuerpo de la solicitud
export const fetchData = async <T, B = undefined>(
    url: string,
    method: 'GET' | 'POST' | 'PUT' = 'GET',
    body?: B,
    headers: HeadersInit = {}
): Promise<T | null> => {
    try {
        const options: RequestInit = {
            method,
            headers: {
                'Content-Type': 'application/json',
                ...headers,
            },
            body: body ? JSON.stringify(body) : undefined,
        };

        const response = await fetch(url, options);

        const data: T = await response.json();

        return data;
    } catch (error) {
        console.error('API ERROR', error);
        return null;
    }
};
