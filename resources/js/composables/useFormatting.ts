import * as _ from 'lodash-es'
import { millify } from 'millify'
import crop from 'crop-url'
import linkifyHtml from 'linkify-html'
import 'linkify-plugin-mention'
import MarkdownIt from 'markdown-it'
import { intlFormatDistance } from 'date-fns'
import type { UserLike } from '@/types/models'

/**
 * Display formatting for numbers, dates, text and user names.
 */
export function useFormatting() {
  function storage(path: string): string {
    return `/storage/${path}`
  }

  function userDisplayName(user: UserLike): string {
    if (!_.isNil(user.name) && '' !== user.name) {
      return user.name
    }

    return user.username
  }

  function userInitials(user: UserLike): string {
    if (!_.isNil(user.name) && !_.isNil(user.last_name)) {
      return _.toUpper(`${user.name.substring(0, 1)}${user.last_name.substring(0, 1)}`)
    }

    return _.toUpper(user.username.substring(0, 1))
  }

  function readable(value: number): string {
    return millify(value)
  }

  function toLocaleDate(date: string | number | Date): string {
    return new Date(date).toLocaleDateString('es-DO', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })
  }

  function relativeDate(date: string | number | Date): string {
    return intlFormatDistance(new Date(date), new Date(), { locale: 'es' })
  }

  function excerpt(text: string): string {
    const len = text.length

    if (len < 400) {
      return text
    }

    return `${text.substring(0, 400)}...`
  }

  function cropUrl(url: string, max = 40): string {
    return crop(url, max)
  }

  function escapeHtml(text: string): string {
    return text
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#39;')
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
    return linkifyHtml(escapeHtml(text), options)
  }

  function markdown(md: string): string {
    return MarkdownIt().render(md)
  }

  function asset(url: string): string {
    return new URL(url, route('home')).toString()
  }

  function karmaMedal(grade: string): string | null {
    let medal: string | null = null

    if (grade === 'C') {
      medal = 'deep-orange-accent-1'
    } else if (grade === 'B') {
      medal = 'blue-grey-lighten-3'
    } else if (grade === 'A') {
      medal = 'amber-accent-4'
    }

    return medal
  }

  return {
    storage,
    userDisplayName,
    userInitials,
    readable,
    toLocaleDate,
    relativeDate,
    excerpt,
    cropUrl,
    linkify,
    markdown,
    asset,
    karmaMedal
  }
}
