export default {
    env: {
      node: true,  // Habilita las variables globales de Node.js como __dirname, process, module, etc.
      es2020: true, // Configura el análisis para ECMAScript 2020
    },
    extends: [
      'eslint:recommended',  // Extiende las configuraciones recomendadas de ESLint
      'plugin:@typescript-eslint/recommended', // Si usas TypeScript
    ],
    parser: '@typescript-eslint/parser', // Si usas TypeScript
    parserOptions: {
      ecmaVersion: 2020, // Utiliza ECMAScript 2020
      sourceType: 'module', // Habilita el uso de módulos (import/export)
    },
    plugins: ['@typescript-eslint'], // Si usas el plugin de TypeScript
    rules: {
      // Aquí puedes personalizar las reglas de ESLint
    },
  };