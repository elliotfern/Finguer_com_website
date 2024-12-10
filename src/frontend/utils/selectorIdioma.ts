export const detectAndRedirect = () => {
  // Idiomas soportados (deben coincidir con los del backend)
  const supportedLanguages = ['es', 'fr', 'en', 'ca'];
  const defaultLanguage = 'es';

  // Obtener el idioma del navegador
  const browserLanguage = navigator.language.slice(0, 2); // Ejemplo: "es-ES" => "es"

  // Verifica si el idioma ya está en la URL
  const currentPath = window.location.pathname;
  const pathLanguage = currentPath.split('/')[1]; // Obtiene el primer segmento de la ruta

  // Si el idioma no está en la URL y es soportado, redirige
  if (!supportedLanguages.includes(pathLanguage)) {
    const targetLanguage = supportedLanguages.includes(browserLanguage) ? browserLanguage : defaultLanguage;

    // Redirige al idioma correcto (sin recargar si ya estás en el idioma por defecto)
    if (targetLanguage !== defaultLanguage) {
      window.location.href = `/${targetLanguage}${currentPath}`;
    }
  }
};
