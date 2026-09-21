import { describe, expect, it } from 'vitest'
import { useFormatting } from '../useFormatting'

const { userDisplayName, userInitials, excerpt, karmaMedal, linkify, formatCount } = useFormatting()

describe('userDisplayName', () => {
  it('prefers the name when present', () => {
    expect(userDisplayName({ name: 'Jane Doe', username: 'jane' })).toBe('Jane Doe')
  })

  it('falls back to the username when the name is empty or absent', () => {
    expect(userDisplayName({ name: '', username: 'jane' })).toBe('jane')
    expect(userDisplayName({ username: 'jane' })).toBe('jane')
  })
})

describe('userInitials', () => {
  it('combines the first letters of the first and last words of the name', () => {
    expect(userInitials({ name: 'jane Mary  doe', username: 'jane' })).toBe('JD')
  })

  it('uses the first letter of the name when it is a single word', () => {
    expect(userInitials({ name: 'maria', username: 'jane' })).toBe('M')
  })

  it('falls back to the first letter of the username when the name is missing or blank', () => {
    expect(userInitials({ username: 'jane' })).toBe('J')
    expect(userInitials({ name: ' ', username: 'jane' })).toBe('J')
  })
})

describe('excerpt', () => {
  it('returns short text unchanged', () => {
    expect(excerpt('short text')).toBe('short text')
  })

  it('truncates text over 400 characters with an ellipsis', () => {
    // Given
    const text = 'a'.repeat(500)

    // When
    const result = excerpt(text)

    // Then
    expect(result).toBe(`${'a'.repeat(400)}...`)
  })
})

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

describe('karmaMedal', () => {
  it('maps known grades to a color token', () => {
    expect(karmaMedal('A')).toBe('amber-accent-4')
    expect(karmaMedal('B')).toBe('blue-grey-lighten-3')
    expect(karmaMedal('C')).toBe('deep-orange-accent-1')
  })

  it('returns null for an unknown grade', () => {
    expect(karmaMedal('Z')).toBeNull()
  })
})

describe('formatCount', () => {
  it('separates thousands with a dot, including on 4-digit numbers', () => {
    expect(formatCount(980)).toBe('980')
    expect(formatCount(3412)).toBe('3.412')
    expect(formatCount(1234567)).toBe('1.234.567')
  })
})
