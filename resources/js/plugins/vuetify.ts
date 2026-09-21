import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { aliases, fa } from 'vuetify/iconsets/fa-svg'
import { themes } from './theme'

const formFieldDefaults = { class: 'mb-4' }

export const vuetify = createVuetify({
  ssr: true,
  defaults: {
    VAutocomplete: formFieldDefaults,
    VCheckbox: formFieldDefaults,
    VCombobox: formFieldDefaults,
    VFileInput: formFieldDefaults,
    VRadioGroup: formFieldDefaults,
    VSelect: formFieldDefaults,
    //VSwitch: formFieldDefaults,
    VTextarea: formFieldDefaults,
    VTextField: formFieldDefaults
  },
  theme: {
    defaultTheme: 'light',
    themes
  },
  icons: {
    defaultSet: 'fa',
    aliases,
    sets: {
      fa
    }
  }
})

/**
 * Vuetify only writes its theme stylesheet to the document in the browser
 * (server-side it needs an unhead instance), so server-rendered HTML would
 * carry `v-theme--*` classes without their `--v-theme-*` variables until
 * hydration. Inertia renders this into the SSR `<head>` and reuses the same
 * element (matched by id) on the client.
 */
export function themeStylesheetHead(): string[] {
  return [`<style id="vuetify-theme-stylesheet">${vuetify.theme.styles.value}</style>`]
}
