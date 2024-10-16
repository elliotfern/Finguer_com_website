export async function fetchData(url: string): Promise<unknown> {
    const token = localStorage.getItem('token');
    const response = await fetch(url, {
        method: 'GET',
        headers: {
            'Authorization': `Bearer ${token}`
        }
    });

    if (!response.ok) {
        throw new Error('Error en la llamada a la API');
    }
    
    return response.json();
}