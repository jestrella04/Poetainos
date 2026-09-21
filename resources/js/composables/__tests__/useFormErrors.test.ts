import { describe, expect, it } from 'vitest'
import { useFormErrors } from '../useFormErrors'

const { validationErrors } = useFormErrors()

describe('validationErrors', () => {
  it('returns the messages Laravel sent for each field', () => {
    // Given
    const error = {
      response: { status: 422, data: { errors: { email: ['Taken'], name: ['Short'] } } }
    }

    // Then
    expect(validationErrors(error)).toEqual({ email: ['Taken'], name: ['Short'] })
  })

  it.each([
    ['a throttled request', { response: { status: 429, data: { message: 'Too Many Attempts.' } } }],
    ['a server error without a body', { response: { status: 500 } }],
    ['a lost connection', { message: 'Network Error' }],
    ['a thrown TypeError', new TypeError('boom')]
  ])('is empty for %s', (_label, error) => {
    // Then
    expect(validationErrors(error)).toEqual({})
  })
})
