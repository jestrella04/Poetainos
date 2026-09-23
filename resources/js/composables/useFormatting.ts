import { escape, head, isNil, last, toUpper, words } from 'lodash-es'
import { millify } from 'millify'
import crop from 'crop-url'
import linkifyHtml from 'linkify-html'
import 'linkify-plugin-mention'
import MarkdownIt, { type Token } from 'markdown-it'
import { intlFormatDistance } from 'date-fns'
import type { UserLike } from '@/types/models'

// Rendered markdown is plain HTML, so Vuetify typography only applies through its utility classes.
const VUETIFY_PROSE_CLASSES: Record<string, string> = {
  h1: 'text-display-small po-prose mb-4',
  h2: 'text-headline-large po-prose mb-4',
  h3: 'text-headline-medium po-prose mb-4',
  h4: 'text-headline-small po-prose mb-4',
  h5: 'text-title-large po-prose mb-4',
  h6: 'text-title-large po-prose mb-4',
  p: 'text-title-large po-prose mb-4',
  ul: 'text-title-large po-prose ml-2 mb-4',
  ol: 'text-title-large po-prose ml-2 mb-4',
  li: 'text-title-large po-prose mb-2',
  blockquote: 'text-title-large po-prose border-s-md border-primary ps-4 mb-4 text-medium-emphasis',
  code: 'text-title-large bg-surface-variant rounded ml-2 px-2 py-1',
  a: 'text-primary'
}

function applyVuetifyClasses(token: Token): void {
  const classes = VUETIFY_PROSE_CLASSES[token.tag]

  // Closing tags (nesting -1) render without attributes.
  if (classes !== undefined && token.nesting !== -1) {
    token.attrJoin('class', classes)
  }
}

const markdownRenderer = new MarkdownIt()

markdownRenderer.core.ruler.push('vuetify_classes', (state) => {
  for (const token of state.tokens) {
    applyVuetifyClasses(token)
    token.children?.forEach(applyVuetifyClasses)
  }
})

const EXCERPT_LENGTH = 400

// Matches config/app.php. Without a pinned zone, the SSR server (UTC) and the browser
// format the same timestamp to different calendar days, which breaks hydration.
const DISPLAY_TIME_ZONE = 'UTC'

const KARMA_MEDALS = new Map([
  ['A', 'amber-accent-4'],
  ['B', 'blue-grey-lighten-3'],
  ['C', 'deep-orange-accent-1']
])

/**
 * Display formatting for numbers, dates, text and user names.
 */
export function useFormatting() {
  function storage(path: string): string {
    return `/storage/${path}`
  }

  function userDisplayName(user: UserLike): string {
    if (!isNil(user.name) && '' !== user.name) {
      return user.name
    }

    return user.username
  }

  function userInitials(user: UserLike): string {
    const nameParts = words(user.name ?? '', /\S+/g)

    if (nameParts.length === 0) {
      return toUpper(user.username.substring(0, 1))
    }

    const lastPart = nameParts.length > 1 ? last(nameParts) : ''

    return toUpper(`${head(nameParts)?.substring(0, 1)}${lastPart?.substring(0, 1)}`)
  }

  function readable(value: number): string {
    return millify(value)
  }

  function formatCount(value: number): string {
    // The plain 'es' locale skips the thousands separator on 4-digit numbers (3412).
    return value.toLocaleString('es-CO')
  }

  function toLocaleDate(date: string | number | Date): string {
    return new Date(date).toLocaleDateString('es-DO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
      timeZone: DISPLAY_TIME_ZONE
    })
  }

  function toLocaleMonthYear(date: string | number | Date): string {
    return new Date(date).toLocaleDateString('es-DO', {
      year: 'numeric',
      month: 'long',
      timeZone: DISPLAY_TIME_ZONE
    })
  }

  function relativeDate(date: string | number | Date): string {
    return intlFormatDistance(new Date(date), new Date(), { locale: 'es' })
  }

  function excerpt(text: string): string {
    if (text.length < EXCERPT_LENGTH) {
      return text
    }

    return `${text.substring(0, EXCERPT_LENGTH)}...`
  }

  function cropUrl(url: string, max = 40): string {
    return crop(url, max)
  }

  function linkify(text: string): string {
    const options = {
      formatHref: {
        mention: (href: string) => `${route('users.index')}${href}`
      }
    }

    // linkify-html parses its input as HTML and re-emits any existing tags
    // verbatim, so raw user text must be entity-escaped first or a comment
    // like `<img src=x onerror=...>` renders live through the `v-html` sink.
    return linkifyHtml(escape(text), options)
  }

  function markdown(md: string): string {
    return markdownRenderer.render(md)
  }

  function asset(url: string): string {
    return new URL(url, route('home')).toString()
  }

  function karmaMedal(grade: string): string | null {
    return KARMA_MEDALS.get(grade) ?? null
  }

  return {
    storage,
    userDisplayName,
    userInitials,
    readable,
    formatCount,
    toLocaleDate,
    toLocaleMonthYear,
    relativeDate,
    excerpt,
    cropUrl,
    linkify,
    markdown,
    asset,
    karmaMedal
  }
}
