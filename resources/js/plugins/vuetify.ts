import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { aliases, fa } from 'vuetify/iconsets/fa-svg'
import { themes } from './theme'

export const vuetify = createVuetify({
  ssr: true,
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
  /* defaults: {
    VCard: {
      rounded: 0,
      elevation: 0,
      border: true
    },
    VBtn: {
      rounded: 0,
      elevation: 0
    },
    VChip: {
      rounded: 0,
      elevation: 0,
      variant: 'flat'
    },
    VTextField: {
      variant: 'underlined',
      density: 'comfortable'
    },
    VTabs: {
      color: 'primary'
    },
    VDivider: {
      color: 'surface-variant'
    }
  } */
})
