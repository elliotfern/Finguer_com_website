import globals from "globals";
import pluginJs from "@eslint/js";
import tseslint from "typescript-eslint";

export default [
  // Ignorar carpetas que no se lintéan
  { ignores: ["dist/**", "node_modules/**"] },

  // Configuración para archivos de configuración (Node.js)
  {
    files: ["**/*.cjs", "**/*.config.js", "**/*.config.cjs", "eslint.config.mjs"],
    languageOptions: {
      globals: globals.node
    }
  },

  // JS / MJS / CJS de frontend (browser)
  {
    files: ["**/*.{js,mjs}"],
    languageOptions: {
      globals: globals.browser
    },
    ...pluginJs.configs.recommended
  },

  // TypeScript (frontend)
  {
    files: ["**/*.ts"],
    languageOptions: {
      globals: globals.browser
    },
    ...tseslint.configs.recommended
  }
];
