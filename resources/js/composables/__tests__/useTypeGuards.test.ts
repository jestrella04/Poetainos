import { describe, expect, it } from 'vitest'
import { useTypeGuards } from '../useTypeGuards'

const { strNullOrEmpty } = useTypeGuards()

describe('strNullOrEmpty', () => {
  it('is true for null, undefined, and blank strings', () => {
    expect(strNullOrEmpty(null)).toBe(true)
    expect(strNullOrEmpty(undefined)).toBe(true)
    expect(strNullOrEmpty('   ')).toBe(true)
  })

  it('is false for a non-blank string', () => {
    expect(strNullOrEmpty('hello')).toBe(false)
  })
})
