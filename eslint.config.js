const globals = require('globals');
const pluginJs = require('@eslint/js');
const tseslint = require('typescript-eslint');

module.exports = tseslint.config(
    {
        ignores: ['dist/**', 'node_modules/**', 'public/dist/**', 'vendor/**'],
    },

    {
        files: ['**/*.{js,mjs}'],
        languageOptions: {
            globals: globals.browser,
        },
        ...pluginJs.configs.recommended,
    },

    {
        files: ['**/*.ts'],
        languageOptions: {
            globals: globals.browser,
        },
    },

    ...tseslint.configs.recommended,

    // Este bloque va AL FINAL para que gane sobre lo anterior
    {
        files: [
            '**/*.cjs',
            '**/*.config.js',
            '**/*.config.cjs',
            'eslint.config.js',
        ],
        languageOptions: {
            globals: globals.node,
        },
        rules: {
            '@typescript-eslint/no-require-imports': 'off',
        },
    }
);
