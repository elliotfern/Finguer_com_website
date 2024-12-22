// api.ts

// Función para hacer las llamadas a la API con un tipo genérico 'T' para la respuesta y 'B' para el cuerpo de la solicitud
export const fetchData = async <T, B = undefined>(
  url: string,
  method: 'GET' | 'POST' | 'PUT' = 'GET',
  body?: B, // El cuerpo es opcional y tiene un tipo 'B'
  headers: HeadersInit = {}
): Promise<T | null> => {
  try {
    // Configurar las opciones de la solicitud
    const options: RequestInit = {
      method, // Método HTTP
      headers: {
        'Content-Type': 'application/json',
        ...headers, // Permite agregar encabezados adicionales
      },
      body: body ? JSON.stringify(body) : undefined, // Solo agregar el cuerpo si existe
    };

    // Hacer la llamada a la API
    const response = await fetch(url, options);

    // Verificar que la respuesta fue exitosa
    if (!response.ok) {
      throw new Error('Error en la llamada a la API');
    }

    // Parsear la respuesta JSON y devolverla con el tipo adecuado
    const data: T = await response.json();
    return data;
  } catch (error) {
    console.error('Hubo un problema con la llamada a la API:', error);
    return null; // O manejar de otra forma
  }
};
