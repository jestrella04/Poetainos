// Ambient declarations for third-party packages that ship no types and have
// no @types/* package available, covering only the API surface this
// codebase actually uses.

declare module 'crop-url' {
  export default function crop(url: string, length: number): string
}

declare module 'linkify-html' {
  interface LinkifyHtmlOptions {
    formatHref?: Record<string, (href: string) => string> | ((href: string, type: string) => string)
  }

  export default function linkifyHtml(html: string, options?: LinkifyHtmlOptions): string
}

// Side-effect-only import that registers the "mention" token type with
// linkifyjs's shared registry; no exports are consumed directly.
declare module 'linkify-plugin-mention'

// The vendored ziggy-js package this project builds against (see the
// `ziggy-js` alias in vite.config.js) ships no types.
declare module 'ziggy-js' {
  import type { Plugin } from 'vue'

  export const ZiggyVue: Plugin
  export function route(
    name: string,
    params?: unknown,
    absolute?: boolean,
    config?: unknown
  ): string
}
