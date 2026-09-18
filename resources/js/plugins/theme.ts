import type { ThemeDefinition } from 'vuetify'
import colors from 'vuetify/util/colors'

export const themes: Record<string, ThemeDefinition> = {
  light: {
    dark: false,
    colors: {
      primary: colors.deepPurple.darken1,
      secondary: colors.blueGrey.lighten1
    }
  },

  dark: {
    dark: true,
    colors: {
      primary: colors.deepPurple.base,
      secondary: colors.blueGrey.base
    }
  }
}

export default themes
