import { describe, expect, it } from 'vitest'
import { useRichText } from '../useRichText'

const { linkify, markdown } = useRichText()

describe('linkify', () => {
  it('escapes raw HTML instead of letting it through to the DOM', () => {
    // Given
    const comment = '<img src=x onerror=alert(1)>'

    // When
    const result = linkify(comment)

    // Then
    expect(result).not.toContain('<img')
    expect(result).toContain('&lt;img')
  })

  it('still turns plain URLs into links', () => {
    // Given
    const comment = 'Check https://example.com out'

    // When
    const result = linkify(comment)

    // Then
    expect(result).toContain('<a href="https://example.com"')
  })

  it('escapes stray angle brackets in plain text so they cannot form a tag', () => {
    // Given
    const comment = '5 < 10 and 10 > 5'

    // When
    const result = linkify(comment)

    // Then
    expect(result).toBe('5 &lt; 10 and 10 &gt; 5')
  })
})

describe('markdown', () => {
  it('adds Vuetify typography classes to block and inline elements', () => {
    // When
    const html = markdown('# Title\n\nSome `code` and a [link](https://example.com).\n\n- item')

    // Then
    expect(html).toContain('<h1 class="text-display-small po-prose mb-4">Title</h1>')
    expect(html).toContain('<p class="text-title-large po-prose mb-4">')
    expect(html).toContain(
      '<code class="text-title-large bg-surface-variant rounded ml-2 px-2 py-1">code</code>'
    )
    expect(html).toContain('<a href="https://example.com" class="text-primary">link</a>')
    expect(html).toContain('<ul class="text-title-large po-prose ml-2 mb-4">')
    expect(html).toContain('<li class="text-title-large po-prose mb-2">item</li>')
  })

  it('leaves elements without a Vuetify mapping unclassed', () => {
    expect(markdown('**bold**')).toContain('<strong>bold</strong>')
  })
})
