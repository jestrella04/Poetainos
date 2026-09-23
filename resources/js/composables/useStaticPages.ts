/**
 * Routes to the site's static CMS pages, keyed by their (Spanish) slugs.
 * Single source of truth for these slugs, otherwise repeated verbatim
 * across every menu/agreement component that links to them.
 */
export function useStaticPages() {
  function faqPath(): string {
    return route('pages.show', 'preguntas-frecuentes')
  }

  function aboutPath(): string {
    return route('pages.show', 'sobre-nosotros')
  }

  function termsPath(): string {
    return route('pages.show', 'condiciones-de-uso')
  }

  function privacyPath(): string {
    return route('pages.show', 'politicas-de-privacidad')
  }

  return { faqPath, aboutPath, termsPath, privacyPath }
}
