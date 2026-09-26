import { router } from '@inertiajs/vue3'

/**
 * Handling for a request failure that isn't a validation error (throttled,
 * server error, offline): keep the user on the page and tell them to try
 * again, instead of Inertia's error page.
 */
export function useRequestFailure() {
  function notifyFailure(): false {
    router.flash({ message: 'main.error-try-again', color: 'error' })

    return false
  }

  /**
   * useHttp() reports a failure through its callbacks and then also rejects;
   * this settles the promise once the callbacks have dealt with it.
   */
  async function whenSettled(request: Promise<unknown>): Promise<void> {
    try {
      await request
    } catch {
      // Already reported through onHttpException / onNetworkError
    }
  }

  return { onHttpException: notifyFailure, onNetworkError: notifyFailure, whenSettled }
}
