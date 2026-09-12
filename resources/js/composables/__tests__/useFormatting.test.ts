import { describe, expect, it } from 'vitest'
import { useFormatting } from '../useFormatting'

const { userDisplayName, userInitials, excerpt, karmaMedal } = useFormatting()

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
  it('combines the first letters of name and last_name when both are present', () => {
    expect(userInitials({ name: 'Jane', last_name: 'Doe', username: 'jane' })).toBe('JD')
  })

  it('falls back to the first letter of the username otherwise', () => {
    expect(userInitials({ username: 'jane' })).toBe('J')
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
