import { createI18n } from 'vue-i18n'
import es from '../i18n/es.json'

export const i18n = createI18n({
  locale: 'es',
  legacy: false,
  messages: { es }
})

/**
 * The regional variant each language formats numbers and dates with. The
 * plain 'es' locale skips the thousands separator on 4-digit numbers (3412).
 */
const REGIONAL_FORMATS: Record<string, { numbers: string; dates: string }> = {
  es: { numbers: 'es-CO', dates: 'es-DO' }
}

export function regionalFormats(): { language: string; numbers: string; dates: string } {
  const language = i18n.global.locale.value

  return { language, ...(REGIONAL_FORMATS[language] ?? { numbers: language, dates: language }) }
}
