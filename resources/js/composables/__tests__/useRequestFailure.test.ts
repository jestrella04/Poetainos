import { describe, expect, it, vi, beforeEach } from 'vitest'
import { useRequestFailure } from '../useRequestFailure'

const mocks = vi.hoisted(() => ({ flash: vi.fn() }))

vi.mock('@inertiajs/vue3', () => ({ router: { flash: mocks.flash } }))

beforeEach(() => {
  mocks.flash.mockClear()
})

describe('useRequestFailure', () => {
  it('tells the user to try again and keeps them on the page', () => {
    // Given
    const { onHttpException, onNetworkError } = useRequestFailure()

    // When
    const httpResult = onHttpException()
    const networkResult = onNetworkError()

    // Then
    expect(httpResult).toBe(false)
    expect(networkResult).toBe(false)
    expect(mocks.flash).toHaveBeenCalledWith({ message: 'main.error-try-again', color: 'error' })
  })

  it('settles a request its callbacks already reported as failed', async () => {
    // Given
    const { whenSettled } = useRequestFailure()

    // When
    const settling = whenSettled(Promise.reject(new Error('network')))

    // Then
    await expect(settling).resolves.toBeUndefined()
  })
})
