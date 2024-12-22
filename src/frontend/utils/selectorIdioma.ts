export const detectAndRedirect = () => {
  // Idiomas soportados (deben coincidir con los del backend)
  const supportedLanguages = ['es', 'fr', 'en', 'ca'];
  const defaultLanguage = 'es';

  // Obtener el idioma del navegador
  const browserLanguage = navigator.language.slice(0, 2); // Ejemplo: "es-ES" => "es"

  // Obtener el idioma desde la cookie si existe
  const getLanguageFromCookie = (): string | null => {
    const match = document.cookie.match(/(?:^|; )language=([^;]*)/);
    return match ? match[1] : null;
  };

  const cookieLanguage = getLanguageFromCookie();

  // Determina el idioma objetivo, primero revisamos la cookie, luego el idioma del navegador
  const targetLanguage = cookieLanguage || (supportedLanguages.includes(browserLanguage) ? browserLanguage : defaultLanguage);

  // Obtener el idioma actual de la URL
  const currentPath = window.location.pathname;
  const pathLanguage = currentPath.split('/')[1]; // Obtiene el primer segmento de la ruta

  // Si el idioma no está en la URL y es soportado, redirige
  if (!supportedLanguages.includes(pathLanguage)) {
    // Si el idioma de destino no es el idioma por defecto, redirige
    if (targetLanguage !== defaultLanguage) {
      window.location.href = `/${targetLanguage}${currentPath}`;
    }
  }
};
