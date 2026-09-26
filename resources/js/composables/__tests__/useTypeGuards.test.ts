import { describe, expect, it } from 'vitest'
import { useTypeGuards } from '../useTypeGuards'

const { isBlank, isEmpty } = useTypeGuards()

describe('isBlank', () => {
  it('is true for null, undefined, and blank strings', () => {
    expect(isBlank(null)).toBe(true)
    expect(isBlank(undefined)).toBe(true)
    expect(isBlank('   ')).toBe(true)
  })

  it('is false for a non-blank string', () => {
    expect(isBlank('hello')).toBe(false)
  })
})

describe('isEmpty', () => {
  it('is true for missing values and empty collections', () => {
    expect(isEmpty(null)).toBe(true)
    expect(isEmpty([])).toBe(true)
    expect(isEmpty({})).toBe(true)
  })

  it('is false for a collection with items', () => {
    expect(isEmpty([1])).toBe(false)
    expect(isEmpty({ id: 1 })).toBe(false)
  })
})
