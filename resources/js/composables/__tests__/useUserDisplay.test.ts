import { describe, expect, it } from 'vitest'
import { useUserDisplay } from '../useUserDisplay'

const { userDisplayName, userInitials, karmaMedal } = useUserDisplay()

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
