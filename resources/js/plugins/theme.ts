import type { ThemeDefinition } from 'vuetify'

export const themes: Record<string, ThemeDefinition> = {
  light: {
    dark: false,
    colors: {
      primary: '#8e5686',
      'on-primary': '#f7f2f6',
      'primary-darken-1': '#6f3f68',
      secondary: '#dfe2da',
      'secondary-darken-1': '#cdd2c7',
      'on-secondary': '#5a6057',
      success: '#059669',
      'success-darken-1': '#047857',
      'on-success': '#FFFFFF',
      info: '#0284C7',
      'info-darken-1': '#0369A1',
      'on-info': '#FFFFFF',
      warning: '#D97706',
      'warning-darken-1': '#B45309',
      'on-warning': '#1e221d',
      error: '#DC2626',
      'error-darken-1': '#B91C1C',
      'on-error': '#FFFFFF',
      background: '#f2f3ef',
      'on-background': '#1e221d',
      surface: '#f2f3ef',
      'on-surface': '#1e221d',
      'surface-variant': '#e4e6e0',
      'on-surface-variant': '#5a6057'
    },

    variables: {
      'code-color': '#8e5686',
      'overlay-scrim-background': '#1e221d',
      'tooltip-background': '#1e221d',
      'overlay-scrim-opacity': 0.5,
      'hover-opacity': 0.04,
      'focus-opacity': 0.1,
      'selected-opacity': 0.08,
      'activated-opacity': 0.16,
      'pressed-opacity': 0.14,
      'dragged-opacity': 0.1,
      'disabled-opacity': 0.4,
      'border-color': '#1e221d',
      'border-opacity': 0.12,
      'table-header-color': '#e4e6e0',
      'high-emphasis-opacity': 0.9,
      'medium-emphasis-opacity': 0.7,

      'shadow-key-umbra-color': '#1e221d',
      'shadow-xs-opacity': '0.16',
      'shadow-sm-opacity': '0.18',
      'shadow-md-opacity': '0.20',
      'shadow-lg-opacity': '0.22',
      'shadow-xl-opacity': '0.24'
    }
  },

  dark: {
    dark: true,
    colors: {
      primary: '#c48fba',
      'on-primary': '#2a1027',
      'primary-darken-1': '#8e5686',
      secondary: '#33362d',
      'secondary-darken-1': '#282b23',
      'on-secondary': '#c7ccc0',
      success: '#34D399',
      'success-darken-1': '#10B981',
      'on-success': '#022C22',
      info: '#38BDF8',
      'info-darken-1': '#0EA5E9',
      'on-info': '#082F49',
      warning: '#FBBF24',
      'warning-darken-1': '#F59E0B',
      'on-warning': '#451A03',
      error: '#F87171',
      'error-darken-1': '#EF4444',
      'on-error': '#450A0A',
      background: '#23261f',
      'on-background': '#e7e8e3',
      surface: '#23261f',
      'on-surface': '#e7e8e3',
      'surface-variant': '#33362d',
      'on-surface-variant': '#9aa093'
    },

    variables: {
      'code-color': '#c48fba',
      'overlay-scrim-background': '#000000',
      'tooltip-background': '#e7e8e3',
      'overlay-scrim-opacity': 0.5,
      'hover-opacity': 0.04,
      'focus-opacity': 0.1,
      'selected-opacity': 0.08,
      'activated-opacity': 0.16,
      'pressed-opacity': 0.14,
      'disabled-opacity': 0.4,
      'dragged-opacity': 0.1,
      'border-color': '#e7e8e3',
      'border-opacity': 0.08,
      'table-header-color': '#33362d',
      'high-emphasis-opacity': 0.9,
      'medium-emphasis-opacity': 0.7,

      'shadow-key-umbra-color': '#000000',
      'shadow-xs-opacity': '0.20',
      'shadow-sm-opacity': '0.22',
      'shadow-md-opacity': '0.24',
      'shadow-lg-opacity': '0.26',
      'shadow-xl-opacity': '0.28'
    }
  }
}

export default themes
