import type { ThemeDefinition } from 'vuetify'

export const themes: Record<string, ThemeDefinition> = {
  light: {
    dark: false,
    colors: {
      background: '#f5fdff',
      'on-background': '#161d1d',
      surface: '#f5fafb',
      'on-surface': '#161d1d',
      'surface-bright': '#f5fafb',
      'surface-light': '#e3e9ea',
      'surface-variant': '#dae4e5',
      'on-surface-variant': '#3f484a',
      primary: '#006971',
      'on-primary': '#ffffff',
      secondary: '#cde7eb',
      'on-secondary': '#324b4e',
      error: '#ba1a1a',
      'on-error': '#ffffff',
      success: '#1e6b3a',
      'on-success': '#ffffff',
      info: '#00658f',
      'on-info': '#ffffff',
      warning: '#8a5100',
      'on-warning': '#ffffff'
    },
    variables: {
      'border-color': '#bec8c9',
      'border-opacity': 1
    }
  },

  dark: {
    dark: true,
    colors: {
      background: '#182022',
      'on-background': '#dee4e4',
      surface: '#0e1415',
      'on-surface': '#dee4e4',
      'surface-bright': '#343a3b',
      'surface-light': '#252b2c',
      'surface-variant': '#3f484a',
      'on-surface-variant': '#bec8c9',
      primary: '#81d3dd',
      'on-primary': '#00363b',
      secondary: '#324b4e',
      'on-secondary': '#cde7eb',
      error: '#ffb4ab',
      'on-error': '#690005',
      success: '#7fd99a',
      'on-success': '#00391c',
      info: '#8ccdff',
      'on-info': '#00344d',
      warning: '#ffb867',
      'on-warning': '#4a2800'
    },
    variables: {
      'border-color': '#3f484a',
      'border-opacity': 1
    }
  }
}

export default themes
