import { describe, expect, it } from 'vitest'
import { Helper } from '../helper'

const helper = new Helper()

describe('strNullOrEmpty', () => {
  it('is true for null, undefined, and blank strings', () => {
    expect(helper.strNullOrEmpty(null)).toBe(true)
    expect(helper.strNullOrEmpty(undefined)).toBe(true)
    expect(helper.strNullOrEmpty('   ')).toBe(true)
  })

  it('is false for a non-blank string', () => {
    expect(helper.strNullOrEmpty('hello')).toBe(false)
  })
})

describe('userDisplayName', () => {
  it('prefers the name when present', () => {
    expect(helper.userDisplayName({ name: 'Jane Doe', username: 'jane' })).toBe('Jane Doe')
  })

  it('falls back to the username when the name is empty or absent', () => {
    expect(helper.userDisplayName({ name: '', username: 'jane' })).toBe('jane')
    expect(helper.userDisplayName({ username: 'jane' })).toBe('jane')
  })
})

describe('userInitials', () => {
  it('combines the first letters of name and last_name when both are present', () => {
    expect(helper.userInitials({ name: 'Jane', last_name: 'Doe', username: 'jane' })).toBe('JD')
  })

  it('falls back to the first letter of the username otherwise', () => {
    expect(helper.userInitials({ username: 'jane' })).toBe('J')
  })
})

describe('excerpt', () => {
  it('returns short text unchanged', () => {
    expect(helper.excerpt('short text')).toBe('short text')
  })

  it('truncates text over 400 characters with an ellipsis', () => {
    const text = 'a'.repeat(500)
    const result = helper.excerpt(text)
    expect(result).toBe(`${'a'.repeat(400)}...`)
  })
})

describe('karmaMedal', () => {
  it('maps known grades to a color token', () => {
    expect(helper.karmaMedal('A')).toBe('amber-accent-4')
    expect(helper.karmaMedal('B')).toBe('blue-grey-lighten-3')
    expect(helper.karmaMedal('C')).toBe('deep-orange-accent-1')
  })

  it('returns null for an unknown grade', () => {
    expect(helper.karmaMedal('Z')).toBeNull()
  })
})

describe('socialLink', () => {
  it('builds a profile URL for a known network', () => {
    expect(helper.socialLink('jane', 'twitter')).toBe('https://twitter.com/jane')
  })

  it('returns an empty string for an unknown network', () => {
    expect(helper.socialLink('jane', 'myspace')).toBe('')
  })
})

describe('setSnackBar / getSnackBar', () => {
  it('round-trips a snack through sessionStorage and clears it after reading', () => {
    helper.setSnackBar({ message: 'Saved', color: 'success', active: true })

    expect(helper.getSnackBar()).toEqual({ message: 'Saved', color: 'success', active: true })
    expect(helper.getSnackBar()).toBeNull()
  })
})

describe('notificationMessage', () => {
  const t = ((key: string) => key) as Parameters<Helper['notificationMessage']>[1]

  it('returns null for an unrecognized notification type', () => {
    const message = helper.notificationMessage(
      { type: 'App\\Notifications\\Unknown', notifier_user: { username: 'jane' } },
      t
    )

    expect(message).toBeNull()
  })

  it('translates a known notification type', () => {
    const message = helper.notificationMessage(
      { type: 'App\\Notifications\\WritingFeatured', notifier_user: { username: 'jane' } },
      t
    )

    expect(message).toBe('writings.writing-awarded')
  })
})
