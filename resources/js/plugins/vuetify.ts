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
})
