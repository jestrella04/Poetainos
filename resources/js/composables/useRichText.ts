import { escape } from 'lodash-es'
import linkifyHtml from 'linkify-html'
import 'linkify-plugin-mention'
import MarkdownIt, { type Token } from 'markdown-it'

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

/**
 * User and admin text turned into HTML: links and @mentions in comments,
 * markdown in pages.
 */
export function useRichText() {
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

  return { linkify, markdown }
}
