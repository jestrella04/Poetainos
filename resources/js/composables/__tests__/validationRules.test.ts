import { describe, expect, it } from 'vitest'
import { PASSWORD_PATTERN, USERNAME_PATTERN } from '../validationRules'

// Browsers anchor a pattern attribute as ^(?:pattern)$
function accepts(pattern: string, value: string): boolean {
  return new RegExp(`^(?:${pattern})$`, 'v').test(value)
}

describe('PASSWORD_PATTERN', () => {
  it.each(['Password1', 'password1A', 'Passw0rd!', 'Abcdefg!', 'aB3defgh'])(
    'accepts %s',
    (password) => {
      expect(accepts(PASSWORD_PATTERN, password)).toBe(true)
    }
  )

  it.each([
    ['too short', 'Pass1'],
    ['no uppercase letter', 'password1'],
    ['no lowercase letter', 'PASSWORD1'],
    ['no digit or symbol', 'Passwordd'],
    ['starting with a dot', '.Password1']
  ])('rejects a password with %s', (_label, password) => {
    expect(accepts(PASSWORD_PATTERN, password)).toBe(false)
  })
})

describe('USERNAME_PATTERN', () => {
  it.each(['emily', 'emily.d', 'Emily_D', 'e1'])('accepts %s', (username) => {
    expect(accepts(USERNAME_PATTERN, username)).toBe(true)
  })

  it.each([
    ['a double dot', 'emily..d'],
    ['a trailing dot', 'emily.'],
    ['a leading dot', '.emily'],
    ['a space', 'emily d'],
    ['more than 45 characters', 'a'.repeat(46)]
  ])('rejects a username with %s', (_label, username) => {
    expect(accepts(USERNAME_PATTERN, username)).toBe(false)
  })
})
