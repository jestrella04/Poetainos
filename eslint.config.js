import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'
import globals from 'globals'

export default [
  {
    ignores: ['node_modules', 'vendor', 'public', 'bootstrap/ssr', 'resources/js/i18n/*.json']
  },
  js.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  {
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        route: 'readonly'
      }
    },
    rules: {
      // Vuetify's data-table dot-named slots (e.g. `item.name`) are parsed as
      // v-slot modifiers by this rule; allow them for that convention.
      'vue/valid-v-slot': ['error', { allowModifiers: true }]
    }
  },
  skipFormatting
]
