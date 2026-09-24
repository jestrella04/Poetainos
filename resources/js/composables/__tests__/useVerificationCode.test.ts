import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest'
import { effectScope } from 'vue'
import type { EffectScope } from 'vue'
import axios from 'axios'
import { useVerificationCode } from '../useVerificationCode'

vi.mock('vue-i18n', () => ({
  useI18n: () => ({ t: (key: string) => key })
}))

let scope: EffectScope

function setUp(): ReturnType<typeof useVerificationCode> {
  scope = effectScope()
  return scope.run(() => useVerificationCode())!
}

beforeEach(() => {
  vi.restoreAllMocks()
  vi.useFakeTimers()
})

afterEach(() => {
  scope.stop()
  vi.useRealTimers()
})

describe('useVerificationCode', () => {
  it('posts the code and hands the redirect url to onVerified', async () => {
    // Given
    const post = vi.spyOn(axios, 'post').mockResolvedValueOnce({ data: { url: '/home' } })
    const onVerified = vi.fn()
    const { code, isVerifying, verifyCode } = setUp()
    code.value = '123456'

    // When
    await verifyCode('/confirm', onVerified)

    // Then
    expect(post).toHaveBeenCalledWith('/confirm', { code: '123456' })
    expect(onVerified).toHaveBeenCalledWith('/home')
    expect(isVerifying.value).toBe(false)
  })

  it('clears a rejected code and shows the server message', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValueOnce({
      response: { data: { errors: { code: ['The code has expired.'] } } }
    })
    const onVerified = vi.fn()
    const { code, codeError, verifyCode } = setUp()
    code.value = '123456'

    // When
    await verifyCode('/confirm', onVerified)

    // Then
    expect(onVerified).not.toHaveBeenCalled()
    expect(code.value).toBe('')
    expect(codeError.value).toBe('The code has expired.')
  })

  it('falls back to a generic message when the rejection has none', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValueOnce({})
    const { codeError, verifyCode } = setUp()

    // When
    await verifyCode('/confirm', vi.fn())

    // Then
    expect(codeError.value).toBe('main.error-try-again')
  })

  it('confirms a resend and blocks another one until the cooldown ends', async () => {
    // Given
    vi.spyOn(axios, 'post').mockResolvedValueOnce({})
    const { codeError, resendOutcome, resendCountdown, resendCode } = setUp()
    codeError.value = 'The code has expired.'

    // When
    await resendCode('/resend')

    // Then
    expect(resendOutcome.value).toBe('success')
    expect(codeError.value).toBe('')
    expect(resendCountdown.value).toBe(60)

    vi.advanceTimersByTime(60_000)

    expect(resendCountdown.value).toBe(0)
    expect(resendOutcome.value).toBeNull()
  })

  it('reports a failed resend without starting the cooldown', async () => {
    // Given
    vi.spyOn(axios, 'post').mockRejectedValueOnce({})
    const { resendOutcome, resendCountdown, resendCode } = setUp()

    // When
    await resendCode('/resend')

    // Then
    expect(resendOutcome.value).toBe('error')
    expect(resendCountdown.value).toBe(0)
  })
})
