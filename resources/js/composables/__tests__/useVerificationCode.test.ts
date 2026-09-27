import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { effectScope } from 'vue'
import type { EffectScope } from 'vue'
import { useVerificationCode } from '../useVerificationCode'
import { queueOutcome, resetFakeRequests, sentRequests } from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseForm, fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useForm: fakeUseForm, useHttp: fakeUseHttp, router: { flash: vi.fn() } }
})

let scope: EffectScope

function setUp(): ReturnType<typeof useVerificationCode> {
  scope = effectScope()
  return scope.run(() => useVerificationCode())!
}

beforeEach(() => {
  resetFakeRequests()
  vi.useFakeTimers()
})

afterEach(() => {
  scope.stop()
  vi.useRealTimers()
})

describe('useVerificationCode', () => {
  it('posts the typed code', async () => {
    // Given
    const { form, verifyCode } = setUp()
    form.code = '123456'

    // When
    verifyCode('/confirm')
    await vi.runAllTimersAsync()

    // Then
    expect(sentRequests).toEqual([{ method: 'post', url: '/confirm', data: { code: '123456' } }])
  })

  it('clears a rejected code and keeps the server message', async () => {
    // Given
    queueOutcome({ errors: { code: 'The code has expired.' } })
    const { form, verifyCode } = setUp()
    form.code = '123456'

    // When
    verifyCode('/confirm')
    await vi.runAllTimersAsync()

    // Then
    expect(form.code).toBe('')
    expect(form.errors.code).toBe('The code has expired.')
  })

  it('confirms a resend and blocks another one until the cooldown ends', async () => {
    // Given
    queueOutcome({ data: {} })
    const { form, resendOutcome, resendCountdown, resendCode } = setUp()
    form.errors.code = 'The code has expired.'

    // When
    await resendCode('/resend')

    // Then
    expect(resendOutcome.value).toBe('success')
    expect(form.errors).toEqual({})
    expect(resendCountdown.value).toBe(60)

    vi.advanceTimersByTime(60_000)

    expect(resendCountdown.value).toBe(0)
    expect(resendOutcome.value).toBeNull()
  })

  it('reports a failed resend without starting the cooldown', async () => {
    // Given
    queueOutcome({ failure: 'http' })
    const { resendOutcome, resendCountdown, resendCode } = setUp()

    // When
    await resendCode('/resend')

    // Then
    expect(resendOutcome.value).toBe('error')
    expect(resendCountdown.value).toBe(0)
  })
})
