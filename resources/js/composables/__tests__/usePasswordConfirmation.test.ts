import { describe, expect, it, vi, beforeEach } from 'vitest'
import { usePasswordConfirmation } from '../usePasswordConfirmation'
import { queueOutcome, resetFakeRequests, sentRequests } from './support/fakeInertiaRequests'

vi.mock('@inertiajs/vue3', async () => {
  const { fakeUseHttp } = await import('./support/fakeInertiaRequests')

  return { useHttp: fakeUseHttp, router: { flash: vi.fn() } }
})

beforeEach(() => {
  resetFakeRequests()
  vi.stubGlobal(
    'route',
    vi.fn((name: string) => name)
  )
})

describe('usePasswordConfirmation', () => {
  describe('confirmThen', () => {
    it('sends the password and runs the action once it is confirmed', async () => {
      // Given
      queueOutcome({ data: {} })
      const action = vi.fn()
      const { passwordConfirmation, confirmThen } = usePasswordConfirmation()
      passwordConfirmation.password = 'Secret-123'

      // When
      await confirmThen(action)

      // Then
      expect(sentRequests).toEqual([
        { method: 'post', url: 'password.confirmer', data: { password: 'Secret-123' } }
      ])
      expect(action).toHaveBeenCalledOnce()
    })

    it('does not run the action when the password is wrong', async () => {
      // Given
      queueOutcome({ errors: { password: 'The provided password is incorrect.' } })
      const action = vi.fn()
      const { passwordConfirmation, confirmThen } = usePasswordConfirmation()

      // When
      await confirmThen(action)

      // Then
      expect(action).not.toHaveBeenCalled()
      expect(passwordConfirmation.errors.password).toBe('The provided password is incorrect.')
    })

    it('does not run the action, nor reject, when the request fails', async () => {
      // Given
      queueOutcome({ failure: 'network' })
      const action = vi.fn()
      const { confirmThen } = usePasswordConfirmation()

      // When
      await confirmThen(action)

      // Then
      expect(action).not.toHaveBeenCalled()
    })
  })
})
