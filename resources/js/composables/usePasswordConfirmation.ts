import { useHttp } from '@inertiajs/vue3'
import { useRequestFailure } from '@/composables/useRequestFailure'

/**
 * Confirms the signed-in user's password before an action the server guards
 * with `password.confirm` (deleting an account), then runs it.
 */
export function usePasswordConfirmation() {
  const passwordConfirmation = useHttp({ password: '' })
  const { onHttpException, onNetworkError, whenSettled } = useRequestFailure()

  async function confirmThen(action: () => void): Promise<void> {
    await whenSettled(
      passwordConfirmation.post(route('password.confirmer'), {
        onHttpException,
        onNetworkError,
        onSuccess: action
      })
    )
  }

  return { passwordConfirmation, confirmThen }
}
