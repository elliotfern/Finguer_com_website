export async function fetchDataGet<T>(relativeUrl: string): Promise<T | null> {
  try {
    const response = await fetch(relativeUrl, {
      method: 'GET',
      credentials: 'include', // 🔴 CLAVE para requireAuthTokenCookie - 'same-origin' para mismo dominio
    });

    if (!response.ok) {
      console.error('Error en la respuesta HTTP', response.status);
      return null;
    }

    const result = await response.json();
    return result as T;

  } catch (error) {
    console.error('Error en fetchDataGet:', error);
    return null;
  }
}
