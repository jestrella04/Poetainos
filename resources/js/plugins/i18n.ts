import { createI18n } from 'vue-i18n'
import es from '../i18n/es.json'

export const i18n = createI18n({
  locale: 'es',
  fallbackLocale: 'en',
  legacy: false,
  messages: { es }
})
