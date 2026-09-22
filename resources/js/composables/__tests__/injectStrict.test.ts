import { describe, expect, it } from 'vitest'
import { createApp } from 'vue'
import type { InjectionKey } from 'vue'
import { injectStrict } from '../injectStrict'

const greetingKey: InjectionKey<string> = Symbol('greeting')

describe('injectStrict', () => {
  it('returns the value an ancestor provided', () => {
    // Given
    const app = createApp({}).provide(greetingKey, 'hola')

    // When
    const injected = app.runWithContext(() => injectStrict(greetingKey))

    // Then
    expect(injected).toBe('hola')
  })

  it('throws an error naming the key when nothing provides it', () => {
    // Given
    const app = createApp({})

    // Then
    expect(() => app.runWithContext(() => injectStrict(greetingKey))).toThrow(
      'Could not resolve Symbol(greeting) injection'
    )
  })
})
