import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import { aliases, fa } from 'vuetify/iconsets/fa-svg'
import colors from 'vuetify/util/colors'

export const vuetify = createVuetify({
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: colors.deepPurple.darken2,
          secondary: colors.blueGrey.lighten1,
          background: colors.grey.lighten2,
          surface: colors.grey.lighten5
        }
      },
      dark: {
        dark: true,
        colors: {
          primary: colors.deepPurple.darken2,
          secondary: colors.blueGrey.darken2
        }
      }
    }
  },
  icons: {
    defaultSet: 'fa',
    aliases,
    sets: {
      fa
    }
  }
})
