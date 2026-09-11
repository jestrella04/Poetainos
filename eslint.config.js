import js from '@eslint/js'
import pluginVue from 'eslint-plugin-vue'
import tseslint from 'typescript-eslint'
import vueParser from 'vue-eslint-parser'
import skipFormatting from '@vue/eslint-config-prettier/skip-formatting'
import globals from 'globals'

export default tseslint.config(
  {
    ignores: [
      'node_modules',
      'vendor',
      'public',
      'bootstrap/ssr',
      'resources/js/i18n/*.json',
      'resources/js/components.d.ts'
    ]
  },
  js.configs.recommended,
  ...pluginVue.configs['flat/essential'],
  ...tseslint.configs.recommendedTypeChecked,
  {
    languageOptions: {
      ecmaVersion: 'latest',
      sourceType: 'module',
      globals: {
        ...globals.browser,
        ...globals.node,
        route: 'readonly'
      },
      parserOptions: {
        project: ['./tsconfig.json', './tsconfig.worker.json'],
        tsconfigRootDir: import.meta.dirname,
        extraFileExtensions: ['.vue']
      }
    },
    rules: {
      // Vuetify's data-table dot-named slots (e.g. `item.name`) are parsed as
      // v-slot modifiers by this rule; allow them for that convention.
      'vue/valid-v-slot': ['error', { allowModifiers: true }],
      // Matches existing `condition && sideEffect()` / ternary-as-statement
      // idioms already used throughout the codebase (e.g. PoPwaPrompt.vue,
      // PoLayoutMain.vue).
      '@typescript-eslint/no-unused-expressions': ['error', { allowShortCircuit: true, allowTernary: true }],
      // TypeScript itself already catches undefined-variable errors, more
      // accurately than this rule can (it doesn't see ambient/global .d.ts
      // types, producing false positives on them).
      'no-undef': 'off'
    }
  },
  {
    // typescript-eslint's type-checked tier sets languageOptions.parser
    // globally; re-point .vue files back at vue-eslint-parser (which
    // delegates the <script> block to the TS parser internally) instead of
    // handing the whole SFC to the TS parser directly.
    files: ['**/*.vue'],
    languageOptions: {
      parser: vueParser,
      parserOptions: {
        parser: tseslint.parser
      }
    }
  },
  {
    // Root-level tool config files aren't part of either tsconfig's
    // `include`, so they can't be type-checked either.
    files: ['*.config.ts', '*.config.js'],
    extends: [tseslint.configs.disableTypeChecked]
  },
  skipFormatting
)
