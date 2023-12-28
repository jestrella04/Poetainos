import 'vuetify/styles'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { aliases, fa } from 'vuetify/iconsets/fa-svg'
import colors from 'vuetify/util/colors'

export const vuetify = createVuetify({
  components,
  directives,
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: colors.deepPurple.base
          //secondary: colors.deepPurple.lighten4
        }
      },
      dark: {
        dark: true,
        colors: {
          primary: colors.deepPurple.base
          //secondary: colors.deepPurple.lighten4
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
