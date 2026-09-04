---
paths:
  - eslint.config.js
---

# General

## vue/valid-v-slot allowModifiers is required for Vuetify data-table slots
Vuetify's `v-data-table-server`/`v-data-table` use dot-named slots like `v-slot:item.name` to target a column. ESLint's `vue/valid-v-slot` parses the text after the dot as a directive modifier, which v-slot doesn't support, and errors. Don't rewrite these templates to dynamic `v-slot:[...]` args — instead keep `vue/valid-v-slot: ['error', { allowModifiers: true }]` set in eslint.config.js. Removing this option will reintroduce ~20+ false-positive lint errors across resources/js/components/admin/*.vue.
